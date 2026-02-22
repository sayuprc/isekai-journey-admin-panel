<?php

declare(strict_types=1);

namespace Creator\Application\Interactors;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use Creator\Application\UseCase\Update\UpdateInputData;
use Creator\Application\UseCase\Update\UpdateOutputData;
use Creator\Application\UseCase\Update\UpdateUseCaseInterface;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Domain\Services\CreatorIntegrityService;
use LogicException;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainRuleViolationError;
use Support\Domain\Error\DomainValidationError;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class UpdateInteractor implements UpdateUseCaseInterface
{
    public function __construct(
        private AuthContext $context,
        private TransactionInterface $transaction,
        private CreatorRepositoryInterface $repository,
        private CreatorIntegrityService $service,
    ) {
    }

    public function handle(UpdateInputData $inputData): Result
    {
        $user = $this->context->get();

        if (is_null($user)) {
            return new Err(new AuthenticationError());
        }

        if (! $user->can(Permission::WriteCreator)) {
            return new Err(new AuthorizationError());
        }

        return $this->transaction->scope(function () use ($inputData): Result {
            $result = $this->service->prepareForUpdate($inputData->creatorId, $inputData->name);

            if ($result->isErr()) {
                return new Err($this->handleError($result->unwrapErr()));
            }

            $creator = $result->unwrap();

            $this->repository->save($creator);

            return new Ok(new UpdateOutputData($creator));
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
