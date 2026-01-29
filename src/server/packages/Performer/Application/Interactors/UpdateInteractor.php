<?php

declare(strict_types=1);

namespace Performer\Application\Interactors;

use Performer\Application\UseCase\Update\UpdateInputData;
use Performer\Application\UseCase\Update\UpdateOutputData;
use Performer\Application\UseCase\Update\UpdateUseCaseInterface;
use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerFactoryInterface;
use Performer\Domain\Models\PerformerRepositoryInterface;
use Performer\Domain\Services\PerformerNameDuplicateCheckService;
use ResultType\Err;
use ResultType\Ok;
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
        return $this->factory->reconstitute($inputData->performerId, $inputData->performerName, $inputData->orderNo)
            // TODO
            ->mapErr(fn (): string => '')
            ->andThen(function (Performer $performer): Result {
                return $this->transaction->scope(function () use ($performer): Result {
                    if ($this->service->existsForUpdate($performer->performerId, $performer->performerName)) {
                        return new Err("Performer name already exists: {$performer->performerName->value}");
                    }

                    $this->repository->save($performer);

                    return new Ok(new UpdateOutputData($performer));
                });
            });
    }
}
