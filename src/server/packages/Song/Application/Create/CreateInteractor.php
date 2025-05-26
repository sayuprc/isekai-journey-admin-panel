<?php

declare(strict_types=1);

namespace Song\Application\Create;

use Song\Domain\Models\SongFactoryInterface;
use Song\Domain\Repositories\SongRepositoryInterface;
use Song\UseCases\Create\CreateInputData;
use Song\UseCases\Create\CreateUseCaseInterface;

class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private readonly SongRepositoryInterface $repository,
        private readonly SongFactoryInterface $factory,
    ) {
    }

    public function handle(CreateInputData $inputData): void
    {
        $song = $this->factory->create(
            $inputData->title,
            $inputData->description,
            $inputData->songTypeId,
            $inputData->orderNo,
            $inputData->lyricists,
            $inputData->composers,
            $inputData->arrangers,
            $inputData->archives,
        );

        $this->repository->insert($song);
    }
}
