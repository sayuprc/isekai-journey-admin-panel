<?php

declare(strict_types=1);

namespace Performer\Application\Interactors;

use Performer\Application\UseCase\Update\UpdateInputData;
use Performer\Application\UseCase\Update\UpdateOutputData;
use Performer\Application\UseCase\Update\UpdateUseCaseInterface;
use Performer\Domain\Models\PerformerFactoryInterface;
use Performer\Domain\Models\PerformerRepositoryInterface;
use Performer\Domain\Services\PerformerNameDuplicateCheckService;
use ResultType\Eager\Err;
use ResultType\Eager\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;

readonly class UpdateInteractor implements UpdateUseCaseInterface
{
    public function __construct(
        private TransactionInterface $transaction,
        private PerformerRepositoryInterface $repository,
        private PerformerFactoryInterface $factory,
        private PerformerNameDuplicateCheckService $service,
    ) {
    }

    public function handle(UpdateInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $performer = $this->factory->reconstitute($inputData->performerId, $inputData->performerName, $inputData->orderNo);

            if ($this->service->existsForUpdate($performer->performerId, $performer->performerName)) {
                return new Err("Performer name already exists: {$inputData->performerName}");
            }

            $this->repository->update($performer);

            return new Ok(new UpdateOutputData($performer));
        });
    }
}
