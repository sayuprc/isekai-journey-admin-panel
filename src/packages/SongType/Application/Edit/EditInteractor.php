<?php

declare(strict_types=1);

namespace SongType\Application\Edit;

use SongType\Domain\Models\SongTypeFactoryInterface;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use SongType\Domain\Services\SongTypeNameDuplicateCheckService;
use SongType\UseCases\Edit\EditRequest;
use SongType\UseCases\Edit\EditUseCaseInterface;
use Support\ResultType\Err;
use Support\ResultType\Ok;
use Support\ResultType\Result;

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
    public function handle(EditRequest $request): Result
    {
        $songType = $this->factory->reconstitute($request->songTypeId, $request->songTypeName, $request->orderNo);

        if ($this->service->existsForUpdate($songType->songTypeId, $songType->songTypeName)) {
            return new Err("Song type already exists: {$request->songTypeId}");
        }

        $this->repository->update($songType);

        return new Ok(null);
    }
}
