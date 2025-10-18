<?php

declare(strict_types=1);

namespace SongType\Application\Interactors;

use ResultType\Eager\Err;
use ResultType\Eager\Ok;
use ResultType\Result;
use SongType\Application\UseCase\Update\UpdateInputData;
use SongType\Application\UseCase\Update\UpdateOutputData;
use SongType\Application\UseCase\Update\UpdateUseCaseInterface;
use SongType\Domain\Models\SongTypeFactoryInterface;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use SongType\Domain\Services\SongTypeNameDuplicateCheckService;
use Support\Contracts\TransactionInterface;

readonly class UpdateInteractor implements UpdateUseCaseInterface
{
    public function __construct(
        private TransactionInterface $transaction,
        private SongTypeRepositoryInterface $repository,
        private SongTypeFactoryInterface $factory,
        private SongTypeNameDuplicateCheckService $service,
    ) {
    }

    public function handle(UpdateInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $songType = $this->factory->reconstitute($inputData->songTypeId, $inputData->songTypeName, $inputData->orderNo);

            if ($this->service->existsForUpdate($songType->songTypeId, $songType->songTypeName)) {
                return new Err("Song type already exists: {$inputData->songTypeId}");
            }

            $this->repository->update($songType);

            return new Ok(new UpdateOutputData($songType));
        });
    }
}
