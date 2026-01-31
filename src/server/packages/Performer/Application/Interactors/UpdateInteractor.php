<?php

declare(strict_types=1);

namespace Performer\Application\Interactors;

use Performer\Application\UseCase\Update\UpdateInputData;
use Performer\Application\UseCase\Update\UpdateOutputData;
use Performer\Application\UseCase\Update\UpdateUseCaseInterface;
use Performer\Domain\Models\PerformerRepositoryInterface;
use Performer\Domain\Services\PerformerIntegrityService;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;

readonly class UpdateInteractor implements UpdateUseCaseInterface
{
    public function __construct(
        private TransactionInterface $transaction,
        private PerformerRepositoryInterface $repository,
        private PerformerIntegrityService $service,
    ) {
    }

    public function handle(UpdateInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $result = $this->service->prepareForUpdate($inputData->performerId, $inputData->performerName, $inputData->orderNo);

            if ($result->isErr()) {
                return new Err($result->unwrapErr());
            }

            $performer = $result->unwrap();

            $this->repository->save($performer);

            return new Ok(new UpdateOutputData($performer));
        });
    }
}
