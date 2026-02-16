<?php

declare(strict_types=1);

namespace AdminUser\Application\Interactors;

use AdminUser\Application\UseCase\Create\CreateInputData;
use AdminUser\Application\UseCase\Create\CreateOutputData;
use AdminUser\Application\UseCase\Create\CreateUseCaseInterface;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Services\AdminUserIntegrityService;
use LogicException;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainRuleViolationError;
use Support\Domain\Error\DomainValidationError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private TransactionInterface $transaction,
        private AdminUserRepositoryInterface $repository,
        private AdminUserIntegrityService $service,
    ) {
    }

    public function handle(CreateInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $result = $this->service->prepareForCreate($inputData->email, $inputData->plainPassword);

            if ($result->isErr()) {
                return new Err($this->handleError($result->unwrapErr()));
            }

            $user = $result->unwrap();

            $this->repository->save($user);

            return new Ok(new CreateOutputData($user));
        });
    }

    private function handleError(DomainError $error): UseCaseError
    {
        return match (true) {
            $error instanceof DomainValidationError => new InvalidInputError($error->errors),
            $error instanceof DomainRuleViolationError => new InvalidInputError([$error->field => [$error->message]]),
            default => throw new LogicException('予期しないドメインエラーが発生しました: ' . $error::class),
        };
    }
}
