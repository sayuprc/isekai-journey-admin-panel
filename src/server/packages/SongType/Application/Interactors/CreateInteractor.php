<?php

declare(strict_types=1);

namespace SongType\Application\Interactors;

use ResultType\Eager\Err;
use ResultType\Eager\Ok;
use ResultType\Result;
use SongType\Application\UseCase\Create\CreateInputData;
use SongType\Application\UseCase\Create\CreateOutputData;
use SongType\Application\UseCase\Create\CreateUseCaseInterface;
use SongType\Domain\Models\SongTypeFactoryInterface;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use SongType\Domain\Services\SongTypeNameDuplicateCheckService;

class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private readonly SongTypeRepositoryInterface $repository,
        private readonly SongTypeFactoryInterface $factory,
        private readonly SongTypeNameDuplicateCheckService $service,
    ) {
    }

    public function handle(CreateInputData $inputData): Result
    {
        $songType = $this->factory->create($inputData->songTypeName, $inputData->orderNo);

        if ($this->service->exists($songType->songTypeName)) {
            return new Err("Song type already exists: {$inputData->songTypeName}");
        }

        $this->repository->insert($songType);

        return new Ok(new CreateOutputData($songType));
    }
}
