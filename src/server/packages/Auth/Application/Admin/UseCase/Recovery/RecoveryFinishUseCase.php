<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\Recovery;

use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use Auth\Domain\Models\AdminUserPasskey;
use Auth\Domain\Models\AdminUserPasskeyRepositoryInterface;
use Auth\Domain\Models\PasskeyCeremonyState;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Auth\Domain\Models\PasskeyCeremonyType;
use Auth\Domain\Models\RecoveryCode\RecoveryCodeId;
use Auth\Domain\Models\RecoveryCode\RecoveryCodeRepositoryInterface;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Auth\Domain\Services\PasskeyRegistrationResult;
use Auth\Domain\Services\Token\AccessToken\AccessTokenIssueService;
use Auth\Domain\Services\Token\RefreshToken\RefreshTokenIssueService;
use LogicException;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\ClockInterface;
use Support\Contracts\TransactionInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;
use Throwable;

readonly class RecoveryFinishUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private AdminUserRepositoryInterface $adminUserRepository,
        private RecoveryCodeRepositoryInterface $recoveryCodeRepository,
        private PasskeyAuthenticatorInterface $passkeyAuthenticator,
        private PasskeyCeremonyStoreInterface $ceremonyStore,
        private AdminUserPasskeyRepositoryInterface $passkeyRepository,
        private RefreshTokenIssueService $refreshTokenIssueService,
        private AccessTokenIssueService $accessTokenIssueService,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private AuditLogRecorderInterface $recorder,
        private UuidGeneratorInterface $uuidGenerator,
        private ClockInterface $clock,
    ) {
    }

    /**
     * @return Result<RecoveryFinishOutputData, UseCaseError>
     */
    public function handle(RecoveryFinishInputData $inputData): Result
    {
        $state = $this->ceremonyStore->pull($inputData->authCeremonyId);

        if (is_null($state) || $state->type !== PasskeyCeremonyType::Recovery) {
            return new Err(new AuthenticationError());
        }

        if (is_null($state->name)) {
            return new Err(new AuthenticationError());
        }

        try {
            $verification = $this->passkeyAuthenticator->finishRegistration(
                $inputData->credential,
                $state->optionsJson,
            );
        } catch (Throwable) {
            return new Err(new AuthenticationError());
        }

        $name = $state->name;

        return $this->transaction->scope(fn (): Result => $this->persist($state, $verification, $name));
    }

    /**
     * @return Result<RecoveryFinishOutputData, UseCaseError>
     */
    private function persist(
        PasskeyCeremonyState $state,
        PasskeyRegistrationResult $verification,
        string $name,
    ): Result {
        // start で検証成功した場合のみ state に本物の adminUserId と検証済みコードの id が
        // 束縛される。ダミー id のときは実在ユーザーが存在しないため find は null となり失敗する。
        $adminUser = $this->adminUserRepository->find(AdminUserId::reconstruct($state->adminUserId));

        if (is_null($adminUser) || is_null($state->recoveryCodeId)) {
            return new Err(new AuthenticationError());
        }

        // start で検証・束縛した当該コードのみを消費する。既に使用済み/存在しない場合は失敗。
        $recoveryCode = $this->recoveryCodeRepository->findUnusedByIdForUpdate(
            RecoveryCodeId::reconstruct($state->recoveryCodeId),
            $adminUser->adminUserId,
        );

        if (is_null($recoveryCode)) {
            return new Err(new AuthenticationError());
        }

        $this->recoveryCodeRepository->save($recoveryCode->consume($this->clock->now()));

        $refreshTokenResult = $this->refreshTokenIssueService->issue($adminUser->adminUserId->value);

        if ($refreshTokenResult->isErr()) {
            return new Err($this->handleError($refreshTokenResult->unwrapErr()));
        }

        ['token' => $refreshToken, 'plainToken' => $plainRefreshToken] = $refreshTokenResult->unwrap();

        $adminUserPasskeyId = $this->uuidGenerator->generate();

        $this->passkeyRepository->save(new AdminUserPasskey(
            $adminUserPasskeyId,
            $adminUser->adminUserId->value,
            $verification->userHandle,
            $name,
            $verification->credentialId,
            $verification->publicKey,
            $verification->aaguid,
            $verification->transports,
            $verification->backupEligible,
            $verification->backupState,
            $verification->signCount,
            $this->clock->now(),
            null,
        ));

        $accessToken = $this->accessTokenIssueService->issue($refreshToken->refreshTokenId->value);

        $this->refreshTokenRepository->save($refreshToken);

        $this->recorder->record(
            AuditAction::RecoveryCodeUse,
            AuditTargetType::AdminUser,
            $adminUser->adminUserId,
            [
                'admin_user_passkey_id' => $adminUserPasskeyId,
                'refresh_token_id' => $refreshToken->refreshTokenId->value,
            ],
            $adminUser->adminUserId,
        );

        return new Ok(new RecoveryFinishOutputData(
            $accessToken,
            $refreshToken->refreshTokenId->value,
            $plainRefreshToken,
        ));
    }

    private function handleError(DomainError $error): UseCaseError
    {
        return match (true) {
            $error instanceof DomainValidationError => new InvalidInputError($error->errors),
            $error instanceof EntityRuleViolationError => new InvalidInputError([$error->field => [$error->message]]),
            default => throw new LogicException('予期しないドメインエラーが発生しました: ' . $error::class),
        };
    }
}
