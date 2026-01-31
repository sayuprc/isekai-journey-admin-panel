<?php

declare(strict_types=1);

namespace Creator\Application\Interactors;

use Creator\Application\UseCase\Create\CreateInputData;
use Creator\Application\UseCase\Create\CreateOutputData;
use Creator\Application\UseCase\Create\CreateUseCaseInterface;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Domain\Services\CreatorIntegrityService;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;

readonly class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private TransactionInterface $transaction,
        private CreatorRepositoryInterface $repository,
        private CreatorIntegrityService $service,
    ) {
    }

    public function handle(CreateInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $result = $this->service->prepareForCreate($inputData->creatorName);

            if ($result->isErr()) {
                return new Err($result->unwrapErr());
            }

            $creator = $result->unwrap();

            $this->repository->save($creator);

            return new Ok(new CreateOutputData($creator));
        });
    }
}
