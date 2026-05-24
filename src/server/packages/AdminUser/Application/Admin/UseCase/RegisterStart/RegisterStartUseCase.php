<?php

declare(strict_types=1);

namespace AdminUser\Application\Admin\UseCase\RegisterStart;

use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Services\AdminUserIntegrityService;
use AdminUser\Domain\Services\RegistrationToken\RegistrationTokenConsumeService;
use Auth\Domain\Models\PasskeyCeremonyState;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Auth\Domain\Models\PasskeyCeremonyType;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use LogicException;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class RegisterStartUseCase
{
    public function __construct(
        private RegistrationTokenConsumeService $consumeService,
        private AdminUserIntegrityService $integrityService,
        private PasskeyAuthenticatorInterface $passkeyAuthenticator,
        private PasskeyCeremonyStoreInterface $ceremonyStore,
        private UuidGeneratorInterface $uuidGenerator,
    ) {
    }

    /**
     * @return Result<RegisterStartOutputData, UseCaseError>
     */
    public function handle(RegisterStartInputData $inputData): Result
    {
        $emailResult = Email::create($inputData->email);

        if ($emailResult->isErr()) {
            return new Err($this->handleError($emailResult->unwrapErr()));
        }

        $tokenResult = $this->consumeService->verify($inputData->plainToken, $emailResult->unwrap());

        if ($tokenResult->isErr()) {
            return new Err($this->handleError($tokenResult->unwrapErr()));
        }

        $token = $tokenResult->unwrap();
        $adminUserResult = $this->integrityService->prepareForCreate(
            $inputData->name,
            $token->email->value,
            $token->role->value,
            $token->permissions->toArray(),
        );

        if ($adminUserResult->isErr()) {
            return new Err($this->handleError($adminUserResult->unwrapErr()));
        }

        $adminUser = $adminUserResult->unwrap();
        $authCeremonyId = $this->uuidGenerator->generate();
        $startResult = $this->passkeyAuthenticator->startRegistration(
            $adminUser->adminUserId->value,
            $adminUser->email->value,
            $adminUser->name->value,
        );

        $this->ceremonyStore->put(new PasskeyCeremonyState(
            $authCeremonyId,
            PasskeyCeremonyType::Register,
            $adminUser->email->value,
            $adminUser->name->value,
            $adminUser->adminUserId->value,
            $startResult->optionsJson,
        ));

        return new Ok(new RegisterStartOutputData($authCeremonyId, $startResult->publicKey));
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
