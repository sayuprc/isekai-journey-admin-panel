<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\Login;

use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use Auth\Domain\Models\AdminUserPasskeyRepositoryInterface;
use Auth\Domain\Models\PasskeyCeremonyState;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use LogicException;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class LoginStartUseCase
{
    public function __construct(
        private AdminUserRepositoryInterface $adminUserRepository,
        private AdminUserPasskeyRepositoryInterface $passkeyRepository,
        private PasskeyAuthenticatorInterface $passkeyAuthenticator,
        private PasskeyCeremonyStoreInterface $ceremonyStore,
        private UuidGeneratorInterface $uuidGenerator,
    ) {
    }

    /**
     * @return Result<LoginStartOutputData, UseCaseError>
     */
    public function handle(LoginStartInputData $inputData): Result
    {
        $emailResult = Email::create($inputData->email);

        if ($emailResult->isErr()) {
            return new Err($this->handleError($emailResult->unwrapErr()));
        }

        $adminUser = $this->adminUserRepository->findByEmail($emailResult->unwrap());

        if ($adminUser === null) {
            return new Err(new AuthenticationError());
        }

        $passkeys = $this->passkeyRepository->findByAdminUserId($adminUser->adminUserId->value);

        if ($passkeys === []) {
            return new Err(new AuthenticationError());
        }

        $authCeremonyId = $this->uuidGenerator->generate();
        $startResult = $this->passkeyAuthenticator->startAuthentication($passkeys);

        $this->ceremonyStore->put(new PasskeyCeremonyState(
            $authCeremonyId,
            'login',
            null,
            $adminUser->email->value,
            null,
            $adminUser->adminUserId->value,
            $startResult->optionsJson,
        ));

        return new Ok(new LoginStartOutputData($authCeremonyId, $startResult->publicKey));
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
