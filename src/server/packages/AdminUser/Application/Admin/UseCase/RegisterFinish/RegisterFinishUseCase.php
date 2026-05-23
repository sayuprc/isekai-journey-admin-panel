<?php

declare(strict_types=1);

namespace AdminUser\Application\Admin\UseCase\RegisterFinish;

use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenRepositoryInterface;
use AdminUser\Domain\Services\AdminUserIntegrityService;
use AdminUser\Domain\Services\RegistrationToken\RegistrationTokenConsumeService;
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
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
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

        if (! $state instanceof PasskeyCeremonyState || $state->type !== 'register') {
            return new Err(new BusinessLogicError('register_ceremony_not_found'));
        }

        if ($state->token === null || $state->name === null) {
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

        return $this->transaction->scope(fn (): Result => $this->persist($state, $verification));
    }

    /**
     * @return Result<RegisterFinishOutputData, UseCaseError>
     */
    private function persist(PasskeyCeremonyState $state, PasskeyVerificationResult $verification): Result
    {
        if ($state->token === null || $state->name === null) {
            return new Err(new BusinessLogicError('register_ceremony_not_found'));
        }

        $emailResult = Email::create($state->email);

        if ($emailResult->isErr()) {
            return new Err($this->handleError($emailResult->unwrapErr()));
        }

        $tokenResult = $this->consumeService->verify($state->token, $emailResult->unwrap());

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

        $adminUser = $this->adminUserRepository->register($adminUser);
        $this->passkeyRepository->save(new AdminUserPasskey(
            $this->uuidGenerator->generate(),
            $adminUser->adminUserId->value,
            $verification->credentialId,
            $verification->publicKey,
            $verification->signCount,
            $this->clock->now(),
            null,
        ));

        $this->registrationTokenRepository->save($token->consume());

        $accessToken = $this->accessTokenIssueService->issue($refreshToken->refreshTokenId->value);

        $this->refreshTokenRepository->save($refreshToken);

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
