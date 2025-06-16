<?php

declare(strict_types=1);

namespace SongType\Application\Create;

use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use SongType\Domain\Models\SongTypeFactoryInterface;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use SongType\Domain\Services\SongTypeNameDuplicateCheckService;
use SongType\UseCases\Create\CreateInputData;
use SongType\UseCases\Create\CreateUseCaseInterface;

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
    public function handle(CreateInputData $inputData): Result
    {
        $songType = $this->factory->create($inputData->songTypeName, $inputData->orderNo);

        if ($this->service->exists($songType->songTypeName)) {
            return new Err("Song type already exists: {$inputData->songTypeName}");
        }

        $this->repository->insert($songType);

        return new Ok(null);
    }
}
