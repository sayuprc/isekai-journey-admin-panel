<?php

declare(strict_types=1);

namespace AdminUser\Application\Interactors;

use Override;
use AdminUser\Application\UseCase\Create\CreateInputData;
use AdminUser\Application\UseCase\Create\CreateOutputData;
use AdminUser\Application\UseCase\Create\CreateUseCaseInterface;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\HashedPassword;
use AdminUser\Domain\Services\AdminUserIntegrityService;
use AdminUser\Domain\Services\HasherInterface;
use LogicException;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private TransactionInterface $transaction,
        private HasherInterface $hasher,
        private AdminUserRepositoryInterface $repository,
        private AdminUserIntegrityService $service,
    ) {
    }

    #[Override]
    public function handle(CreateInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $adminUserResult = $this->service->prepareForCreate(
                $inputData->name,
                $inputData->email,
                $inputData->role,
                $inputData->permissions,
            );

            if ($adminUserResult->isErr()) {
                return new Err($this->handleError($adminUserResult->unwrapErr()));
            }

            $passwordResult = HashedPassword::create($this->hasher->hash($inputData->plainPassword));

            if ($passwordResult->isErr()) {
                return new Err($this->handleError($passwordResult->unwrapErr()));
            }

            $adminUser = $this->repository->register($adminUserResult->unwrap(), $passwordResult->unwrap());

            return new Ok(new CreateOutputData($adminUser));
        });
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
