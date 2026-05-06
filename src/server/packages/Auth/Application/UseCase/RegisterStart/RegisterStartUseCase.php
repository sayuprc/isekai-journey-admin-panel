<?php

declare(strict_types=1);

namespace Auth\Application\UseCase\RegisterStart;

use AdminUser\Domain\Models\AdminUserRegistrationToken;
use AdminUser\Domain\Models\AdminUserRegistrationTokenRepositoryInterface;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Services\RegistrationTokenHasherInterface;
use Auth\Domain\Models\PasskeyCeremonyState;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use LogicException;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\ClockInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class RegisterStartUseCase
{
    public function __construct(
        private ClockInterface $clock,
        private UuidGeneratorInterface $uuidGenerator,
        private RegistrationTokenHasherInterface $hasher,
        private AdminUserRegistrationTokenRepositoryInterface $tokenRepository,
        private PasskeyAuthenticatorInterface $passkeyAuthenticator,
        private PasskeyCeremonyStoreInterface $ceremonyStore,
    ) {
    }

    /**
     * @return Result<RegisterStartOutputData, UseCaseError>
     */
    public function handle(RegisterStartInputData $inputData): Result
    {
        $emailResult = Email::create($inputData->email);
        if ($emailResult->isErr()) {
            return new Err($this->handleEmailError($emailResult->unwrapErr()));
        }

        $matchedToken = $this->resolveMatchedToken($emailResult->unwrap(), $inputData->registrationToken);

        if ($matchedToken === null) {
            return new Err(new AuthenticationError());
        }

        $adminUserId = $this->uuidGenerator->generate();
        $authCeremonyId = $this->uuidGenerator->generate();

        $result = $this->passkeyAuthenticator->startRegistration(
            $adminUserId,
            $matchedToken->email->value,
            $matchedToken->name->value,
        );

        $this->ceremonyStore->put(
            new PasskeyCeremonyState(
                $authCeremonyId,
                'register',
                $matchedToken->email->value,
                $result->optionsJson,
                $adminUserId,
                $matchedToken->adminUserRegistrationTokenId->value,
            ),
        );

        return new Ok(new RegisterStartOutputData($authCeremonyId, $result->publicKey));
    }

    private function resolveMatchedToken(Email $email, string $registrationToken): ?AdminUserRegistrationToken
    {
        foreach ($this->tokenRepository->findByEmail($email) as $token) {
            if (! $this->hasher->verify($registrationToken, $token->tokenHash->value)) {
                continue;
            }

            if ($token->usedAt !== null || $token->expiredAt->value <= $this->clock->now()) {
                return null;
            }

            return $token;
        }

        return null;
    }

    private function handleEmailError(DomainError $error): UseCaseError
    {
        return match (true) {
            $error instanceof DomainValidationError => new InvalidInputError($error->errors),
            $error instanceof EntityRuleViolationError => new InvalidInputError([$error->field => [$error->message]]),
            default => throw new LogicException('予期しないドメインエラーが発生しました: ' . $error::class),
        };
    }
}
