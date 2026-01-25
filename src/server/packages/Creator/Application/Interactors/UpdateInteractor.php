<?php

declare(strict_types=1);

namespace Creator\Application\Interactors;

use Creator\Application\UseCase\Update\UpdateInputData;
use Creator\Application\UseCase\Update\UpdateOutputData;
use Creator\Application\UseCase\Update\UpdateUseCaseInterface;
use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Domain\Services\CreatorNameDuplicateCheckService;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;

readonly class UpdateInteractor implements UpdateUseCaseInterface
{
    public function __construct(
        private TransactionInterface $transaction,
        private CreatorRepositoryInterface $repository,
        private CreatorFactoryInterface $factory,
        private CreatorNameDuplicateCheckService $service,
    ) {
    }

    public function handle(UpdateInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $creator = $this->factory->reconstitute($inputData->creatorId, $inputData->creatorName);

            if ($this->service->exists($creator->creatorName)) {
                return new Err("Creator name already exists: {$inputData->creatorName}");
            }

            $this->repository->save($creator);

            return new Ok(new UpdateOutputData($creator));
        });
    }
}
