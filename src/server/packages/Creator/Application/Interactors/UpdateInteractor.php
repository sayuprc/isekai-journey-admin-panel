<?php

declare(strict_types=1);

namespace Creator\Application\Interactors;

use Creator\Application\UseCase\Update\UpdateInputData;
use Creator\Application\UseCase\Update\UpdateOutputData;
use Creator\Application\UseCase\Update\UpdateUseCaseInterface;
use Creator\Domain\Models\Creator;
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
        return $this->factory->reconstitute($inputData->creatorId, $inputData->creatorName)
            ->mapErr(fn (): string => '')
            ->andThen(function (Creator $creator): Result {
                return $this->transaction->scope(function () use ($creator): Result {
                    if ($this->service->exists($creator->creatorName)) {
                        return new Err("Creator name already exists: {$creator->creatorName->value}");
                    }

                    $this->repository->save($creator);

                    return new Ok(new UpdateOutputData($creator));
                });
            });
    }
}
