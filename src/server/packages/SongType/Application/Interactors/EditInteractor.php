<?php

declare(strict_types=1);

namespace SongType\Application\Interactors;

use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use SongType\Application\UseCase\Edit\EditInputData;
use SongType\Application\UseCase\Edit\EditUseCaseInterface;
use SongType\Domain\Models\SongTypeFactoryInterface;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use SongType\Domain\Services\SongTypeNameDuplicateCheckService;

class EditInteractor implements EditUseCaseInterface
{
    public function __construct(
        private readonly SongTypeRepositoryInterface $repository,
        private readonly SongTypeFactoryInterface $factory,
        private readonly SongTypeNameDuplicateCheckService $service,
    ) {
    }

    /**
     * @return Result<null, string>
     */
    public function handle(EditInputData $inputData): Result
    {
        $songType = $this->factory->reconstitute($inputData->songTypeId, $inputData->songTypeName, $inputData->orderNo);

        if ($this->service->existsForUpdate($songType->songTypeId, $songType->songTypeName)) {
            return new Err("Song type already exists: {$inputData->songTypeId}");
        }

        $this->repository->update($songType);

        return new Ok(null);
    }
}
