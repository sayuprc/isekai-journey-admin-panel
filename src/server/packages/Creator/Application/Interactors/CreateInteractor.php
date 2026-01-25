<?php

declare(strict_types=1);

namespace Creator\Application\Interactors;

use Creator\Application\UseCase\Create\CreateInputData;
use Creator\Application\UseCase\Create\CreateOutputData;
use Creator\Application\UseCase\Create\CreateUseCaseInterface;
use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Domain\Services\CreatorNameDuplicateCheckService;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;

readonly class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private TransactionInterface $transaction,
        private CreatorRepositoryInterface $repository,
        private CreatorFactoryInterface $factory,
        private CreatorNameDuplicateCheckService $service,
    ) {
    }

    public function handle(CreateInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $creator = $this->factory->create($inputData->creatorName);

            if ($this->service->exists($creator->creatorName)) {
                return new Err("Creator name already exists: {$inputData->creatorName}");
            }

            $this->repository->save($creator);

            return new Ok(new CreateOutputData($creator));
        });
    }
}
