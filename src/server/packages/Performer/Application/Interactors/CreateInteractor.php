<?php

declare(strict_types=1);

namespace Performer\Application\Interactors;

use Performer\Application\UseCase\Create\CreateInputData;
use Performer\Application\UseCase\Create\CreateOutputData;
use Performer\Application\UseCase\Create\CreateUseCaseInterface;
use Performer\Domain\Models\PerformerFactoryInterface;
use Performer\Domain\Models\PerformerRepositoryInterface;
use Performer\Domain\Services\PerformerNameDuplicateCheckService;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;

readonly class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private TransactionInterface $transaction,
        private PerformerRepositoryInterface $repository,
        private PerformerFactoryInterface $factory,
        private PerformerNameDuplicateCheckService $service,
    ) {
    }

    public function handle(CreateInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $performer = $this->factory->create($inputData->performerName, $inputData->orderNo);

            if ($this->service->exists($performer->performerName)) {
                return new Err("Performer name already exists: {$inputData->performerName}");
            }

            $this->repository->save($performer);

            return new Ok(new CreateOutputData($performer));
        });
    }
}
