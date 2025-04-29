<?php

declare(strict_types=1);

namespace SongType\Application\Create;

use SongType\Domain\Models\SongTypeFactoryInterface;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use SongType\Domain\Services\SongTypeNameDuplicateCheckService;
use SongType\UseCases\Create\CreateRequest;
use SongType\UseCases\Create\CreateUseCaseInterface;
use Support\ResultType\Err;
use Support\ResultType\Ok;
use Support\ResultType\Result;

class CreateInteractor implements CreateUseCaseInterface
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
    public function handle(CreateRequest $request): Result
    {
        $songType = $this->factory->create($request->songTypeName, $request->orderNo);

        if ($this->service->exists($songType->songTypeName)) {
            return new Err("Song type already exists: {$request->songTypeName}");
        }

        $this->repository->insert($songType);

        return new Ok(null);
    }
}
