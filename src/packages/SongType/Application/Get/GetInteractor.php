<?php

declare(strict_types=1);

namespace SongType\Application\Get;

use SongType\Domain\Models\SongTypeId;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use SongType\UseCases\Get\GetRequest;
use SongType\UseCases\Get\GetResponse;
use SongType\UseCases\Get\GetUseCaseInterface;
use Support\ResultType\Err;
use Support\ResultType\Ok;
use Support\ResultType\Result;

class GetInteractor implements GetUseCaseInterface
{
    public function __construct(private readonly SongTypeRepositoryInterface $repository)
    {
    }

    /**
     * @return Result<GetResponse, string>
     */
    public function handle(GetRequest $request): Result
    {
        if (is_null($found = $this->repository->find(new SongTypeId($request->songTypeId)))) {
            return new Err("Song type not found: {$request->songTypeId}");
        }

        return new Ok(new GetResponse($found));
    }
}
