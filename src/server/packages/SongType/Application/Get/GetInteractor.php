<?php

declare(strict_types=1);

namespace SongType\Application\Get;

use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use SongType\Domain\Models\SongTypeId;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use SongType\UseCases\Get\GetInputData;
use SongType\UseCases\Get\GetOutputData;
use SongType\UseCases\Get\GetUseCaseInterface;

class GetInteractor implements GetUseCaseInterface
{
    public function __construct(private readonly SongTypeRepositoryInterface $repository)
    {
    }

    /**
     * @return Result<GetOutputData, string>
     */
    public function handle(GetInputData $inputData): Result
    {
        if (is_null($found = $this->repository->find(new SongTypeId($inputData->songTypeId)))) {
            return new Err("Song type not found: {$inputData->songTypeId}");
        }

        return new Ok(new GetOutputData($found));
    }
}
