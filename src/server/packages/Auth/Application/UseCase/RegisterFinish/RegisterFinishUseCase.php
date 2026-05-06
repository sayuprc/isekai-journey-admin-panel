<?php

declare(strict_types=1);

namespace Auth\Application\UseCase\RegisterFinish;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\AdminUserRegistrationTokenId;
use AdminUser\Domain\Models\AdminUserRegistrationTokenRepositoryInterface;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\HashedPassword;
use AdminUser\Domain\Services\AdminUserIntegrityService;
use AdminUser\Domain\Services\HasherInterface;
use Auth\Domain\Models\AdminUserPasskey;
use Auth\Domain\Models\AdminUserPasskeyRepositoryInterface;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
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

readonly class RegisterFinishUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private ClockInterface $clock,
        private UuidGeneratorInterface $uuidGenerator,
        private HasherInterface $hasher,
        private AdminUserIntegrityService $adminUserIntegrityService,
        private AdminUserRepositoryInterface $adminUserRepository,
        private AdminUserRegistrationTokenRepositoryInterface $tokenRepository,
        private AdminUserPasskeyRepositoryInterface $passkeyRepository,
        private PasskeyAuthenticatorInterface $passkeyAuthenticator,
        private PasskeyCeremonyStoreInterface $ceremonyStore,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private RefreshTokenIssueService $refreshTokenIssueService,
        private AccessTokenIssueService $accessTokenIssueService,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    /**
     * @return Result<RegisterFinishOutputData, UseCaseError>
     */
    public function handle(RegisterFinishInputData $inputData): Result
    {
        $state = $this->ceremonyStore->pull($inputData->authCeremonyId);

        if ($state === null || $state->type !== 'register' || $state->registrationTokenId === null || $state->adminUserId === null) {
            return new Err(new AuthenticationError());
        }

        $token = $this->tokenRepository->find(AdminUserRegistrationTokenId::reconstruct($state->registrationTokenId));

        if ($token === null || $token->usedAt !== null || $token->expiredAt->value <= $this->clock->now()) {
            return new Err(new AuthenticationError());
        }

        try {
            $verificationResult = $this->passkeyAuthenticator->finishRegistration($inputData->credential, $state->optionsJson);
        } catch (Throwable) {
            return new Err(new AuthenticationError());
        }

        return $this->transaction->scope(function () use ($state, $token, $verificationResult): Result {
            $adminUserResult = $this->adminUserIntegrityService->prepareForCreateWithId(
                $state->adminUserId,
                $token->name->value,
                $token->email->value,
                $token->role->value,
                [],
            );

            if ($adminUserResult->isErr()) {
                return new Err($this->handleError($adminUserResult->unwrapErr()));
            }

            $this->appRegisterUser($adminUserResult->unwrap());

            $passkey = new AdminUserPasskey(
                $this->uuidGenerator->generate(),
                $state->adminUserId,
                $verificationResult->credentialId,
                $verificationResult->publicKey,
                $verificationResult->signCount,
                $this->clock->now(),
                null,
            );

            $this->passkeyRepository->save($passkey);
            $this->tokenRepository->markUsed($token->adminUserRegistrationTokenId, $this->clock->now());

            $refreshResult = $this->refreshTokenIssueService->issue($state->adminUserId);

            if ($refreshResult->isErr()) {
                return new Err($this->handleError($refreshResult->unwrapErr()));
            }

            ['token' => $refreshToken, 'plainToken' => $plainToken] = $refreshResult->unwrap();

            $accessToken = $this->accessTokenIssueService->issue($refreshToken->refreshTokenId->value);

            $this->refreshTokenRepository->save($refreshToken);
            $this->recorder->record(
                AuditAction::Login,
                AuditTargetType::AdminUser,
                AdminUserId::reconstruct($state->adminUserId),
                ['refresh_token_id' => $refreshToken->refreshTokenId->value],
                AdminUserId::reconstruct($state->adminUserId),
            );

            return new Ok(new RegisterFinishOutputData($accessToken, $refreshToken->refreshTokenId->value, $plainToken));
        });
    }

    private function appRegisterUser(AdminUser $adminUser): void
    {
        $password = bin2hex(random_bytes(32));

        $this->adminUserRepository->register(
            $adminUser,
            HashedPassword::reconstruct($this->hasher->hash($password)),
        );
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
