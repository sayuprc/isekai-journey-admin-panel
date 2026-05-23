<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\Login;

use Auth\Domain\Models\AdminUserPasskey;
use Auth\Domain\Models\AdminUserPasskeyRepositoryInterface;
use Auth\Domain\Models\PasskeyCeremonyState;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Auth\Domain\Services\PasskeyVerificationResult;
use Auth\Domain\Services\Token\AccessToken\AccessTokenIssueService;
use Auth\Domain\Services\Token\RefreshToken\RefreshTokenIssueService;
use LogicException;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\ClockInterface;
use Support\Contracts\TransactionInterface;
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

readonly class LoginFinishUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private PasskeyCeremonyStoreInterface $ceremonyStore,
        private AdminUserPasskeyRepositoryInterface $passkeyRepository,
        private PasskeyAuthenticatorInterface $passkeyAuthenticator,
        private RefreshTokenIssueService $refreshTokenIssueService,
        private AccessTokenIssueService $accessTokenIssueService,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private AuditLogRecorderInterface $recorder,
        private ClockInterface $clock,
    ) {
    }

    /**
     * @return Result<LoginFinishOutputData, UseCaseError>
     */
    public function handle(LoginFinishInputData $inputData): Result
    {
        $state = $this->ceremonyStore->pull($inputData->authCeremonyId);

        if (! $state instanceof PasskeyCeremonyState || $state->type !== 'login') {
            return new Err(new AuthenticationError());
        }

        $credentialId = $this->credentialId($inputData->credential);

        if ($credentialId === null) {
            return new Err(new AuthenticationError());
        }

        $passkey = $this->passkeyRepository->findByCredentialId($credentialId);

        if ($passkey === null || $passkey->adminUserId !== $state->adminUserId) {
            return new Err(new AuthenticationError());
        }

        try {
            $verification = $this->passkeyAuthenticator->finishAuthentication(
                $inputData->credential,
                $state->optionsJson,
                $passkey,
                $state->adminUserId,
            );
        } catch (Throwable) {
            return new Err(new AuthenticationError());
        }

        if ($verification->credentialId !== $passkey->credentialId) {
            return new Err(new AuthenticationError());
        }

        return $this->transaction->scope(
            fn (): Result => $this->persist($state, $passkey, $verification),
        );
    }

    /**
     * @return Result<LoginFinishOutputData, UseCaseError>
     */
    private function persist(
        PasskeyCeremonyState $state,
        AdminUserPasskey $passkey,
        PasskeyVerificationResult $verification,
    ): Result {
        $refreshTokenResult = $this->refreshTokenIssueService->issue($state->adminUserId);

        if ($refreshTokenResult->isErr()) {
            return new Err($this->handleError($refreshTokenResult->unwrapErr()));
        }

        ['token' => $refreshToken, 'plainToken' => $plainRefreshToken] = $refreshTokenResult->unwrap();

        $this->passkeyRepository->update($passkey->withCounter($verification->signCount, $this->clock->now()));

        $accessToken = $this->accessTokenIssueService->issue($refreshToken->refreshTokenId->value);

        $this->refreshTokenRepository->save($refreshToken);

        $this->recorder->record(
            AuditAction::Login,
            AuditTargetType::AdminUser,
            $refreshToken->adminUserId,
            [
                'refresh_token_id' => $refreshToken->refreshTokenId->value,
                'admin_user_passkey_id' => $passkey->adminUserPasskeyId,
            ],
            $refreshToken->adminUserId,
        );

        return new Ok(new LoginFinishOutputData(
            $accessToken,
            $refreshToken->refreshTokenId->value,
            $plainRefreshToken,
        ));
    }

    /**
     * @param array<string, mixed> $credential
     */
    private function credentialId(array $credential): ?string
    {
        $id = $credential['id'] ?? null;

        return is_string($id) && $id !== '' ? $id : null;
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
