<?php

declare(strict_types=1);

namespace Performer\Application\Interactors;

use LogicException;
use Performer\Application\UseCase\Create\CreateInputData;
use Performer\Application\UseCase\Create\CreateOutputData;
use Performer\Application\UseCase\Create\CreateUseCaseInterface;
use Performer\Domain\Models\PerformerRepositoryInterface;
use Performer\Domain\Services\PerformerIntegrityService;
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
        private PerformerRepositoryInterface $repository,
        private PerformerIntegrityService $service,
    ) {
    }

    public function handle(CreateInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $result = $this->service->prepareForCreate($inputData->performerName);

            if ($result->isErr()) {
                return new Err($this->handleError($result->unwrapErr()));
            }

            $performer = $result->unwrap();

            $this->repository->save($performer);

            return new Ok(new CreateOutputData($performer));
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
