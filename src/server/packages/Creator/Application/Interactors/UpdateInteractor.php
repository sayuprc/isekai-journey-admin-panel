<?php

declare(strict_types=1);

namespace Creator\Application\Interactors;

use Creator\Application\UseCase\Update\UpdateInputData;
use Creator\Application\UseCase\Update\UpdateOutputData;
use Creator\Application\UseCase\Update\UpdateUseCaseInterface;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Domain\Services\CreatorIntegrityService;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;

readonly class UpdateInteractor implements UpdateUseCaseInterface
{
    public function __construct(
        private TransactionInterface $transaction,
        private CreatorRepositoryInterface $repository,
        private CreatorIntegrityService $service,
    ) {
    }

    public function handle(UpdateInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $result = $this->service->prepareForUpdate($inputData->creatorId, $inputData->creatorName);

            if ($result->isErr()) {
                return new Err($result->unwrapErr());
            }

            $creator = $result->unwrap();

            $this->repository->save($creator);

            return new Ok(new UpdateOutputData($creator));
        });
    }
}
