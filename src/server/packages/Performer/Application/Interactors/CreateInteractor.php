<?php

declare(strict_types=1);

namespace Performer\Application\Interactors;

use Performer\Application\UseCase\Create\CreateInputData;
use Performer\Application\UseCase\Create\CreateOutputData;
use Performer\Application\UseCase\Create\CreateUseCaseInterface;
use Performer\Domain\Models\PerformerRepositoryInterface;
use Performer\Domain\Services\PerformerIntegrityService;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;

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
            $result = $this->service->prepareForCreate($inputData->performerName, $inputData->orderNo);

            if ($result->isErr()) {
                return new Err($result->unwrapErr());
            }

            $performer = $result->unwrap();

            $this->repository->save($performer);

            return new Ok(new CreateOutputData($performer));
        });
    }
}
