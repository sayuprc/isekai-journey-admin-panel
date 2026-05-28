<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\RegisterFinish;

use AdminUser\Domain\Exceptions\DuplicateAdminUserEmailException;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenRepositoryInterface;
use AdminUser\Domain\Services\AdminUserIntegrityService;
use AdminUser\Domain\Services\RegistrationToken\RegistrationTokenConsumeService;
use Auth\Domain\Models\AdminUserPasskey;
use Auth\Domain\Models\AdminUserPasskeyRepositoryInterface;
use Auth\Domain\Models\PasskeyCeremonyState;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Auth\Domain\Models\PasskeyCeremonyType;
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
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;
use Throwable;

readonly class RegisterFinishUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private AdminUserRepositoryInterface $adminUserRepository,
        private AdminUserIntegrityService $integrityService,
        private RegistrationTokenConsumeService $consumeService,
        private RegistrationTokenRepositoryInterface $registrationTokenRepository,
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
     * @return Result<RegisterFinishOutputData, UseCaseError>
     */
    public function handle(RegisterFinishInputData $inputData): Result
    {
        $state = $this->ceremonyStore->pull($inputData->authCeremonyId);

        if (is_null($state) || $state->type !== PasskeyCeremonyType::Register) {
            return new Err(new BusinessLogicError('register_ceremony_not_found'));
        }

        if (is_null($state->name)) {
            return new Err(new BusinessLogicError('register_ceremony_not_found'));
        }

        try {
            $verification = $this->passkeyAuthenticator->finishRegistration(
                $inputData->credential,
                $state->optionsJson,
            );
        } catch (Throwable) {
            return new Err(new BusinessLogicError('passkey_verification_failed'));
        }

        return $this->transaction->scope(fn (): Result => $this->persist($state, $inputData->plainToken, $verification));
    }

    /**
     * @return Result<RegisterFinishOutputData, UseCaseError>
     */
    private function persist(
        PasskeyCeremonyState $state,
        string $plainToken,
        PasskeyRegistrationResult $verification,
    ): Result {
        if (is_null($state->name)) {
            return new Err(new BusinessLogicError('register_ceremony_not_found'));
        }

        $emailResult = Email::create($state->email);

        if ($emailResult->isErr()) {
            return new Err($this->handleError($emailResult->unwrapErr()));
        }

        $tokenResult = $this->consumeService->verify($plainToken, $emailResult->unwrap());

        if ($tokenResult->isErr()) {
            return new Err($this->handleError($tokenResult->unwrapErr()));
        }

        $token = $tokenResult->unwrap();
        $adminUserResult = $this->integrityService->prepareForCreateWithId(
            $state->adminUserId,
            $state->name,
            $token->email->value,
            $token->role->value,
            $token->permissions->toArray(),
        );

        if ($adminUserResult->isErr()) {
            return new Err($this->handleError($adminUserResult->unwrapErr()));
        }

        $adminUser = $adminUserResult->unwrap();
        $refreshTokenResult = $this->refreshTokenIssueService->issue($adminUser->adminUserId->value);

        if ($refreshTokenResult->isErr()) {
            return new Err($this->handleError($refreshTokenResult->unwrapErr()));
        }

        ['token' => $refreshToken, 'plainToken' => $plainRefreshToken] = $refreshTokenResult->unwrap();

        try {
            $adminUser = $this->adminUserRepository->register($adminUser);
        } catch (DuplicateAdminUserEmailException $exception) {
            return new Err(new BusinessLogicError($exception->getMessage()));
        }

        $adminUserPasskeyId = $this->uuidGenerator->generate();

        $this->passkeyRepository->save(new AdminUserPasskey(
            $adminUserPasskeyId,
            $adminUser->adminUserId->value,
            $verification->userHandle,
            $state->name,
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

        $this->registrationTokenRepository->save($token->consume());

        $accessToken = $this->accessTokenIssueService->issue($refreshToken->refreshTokenId->value);

        $this->refreshTokenRepository->save($refreshToken);

        $this->recorder->record(
            AuditAction::Register,
            AuditTargetType::AdminUser,
            $adminUser->adminUserId,
            [
                'admin_user_passkey_id' => $adminUserPasskeyId,
                'refresh_token_id' => $refreshToken->refreshTokenId->value,
            ],
            $adminUser->adminUserId,
        );

        return new Ok(new RegisterFinishOutputData(
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
            $error instanceof BusinessRuleViolationError => new BusinessLogicError($error->message),
            default => throw new LogicException('予期しないドメインエラーが発生しました: ' . $error::class),
        };
    }
}
