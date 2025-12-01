<?php

declare(strict_types=1);

namespace Song\Application\Interactors;

use Song\Application\UseCase\Create\CreateInputData;
use Song\Application\UseCase\Create\CreateUseCaseInterface;
use Song\Domain\Models\SongFactoryInterface;
use Song\Domain\Models\SongRepositoryInterface;

readonly class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private SongRepositoryInterface $repository,
        private SongFactoryInterface $factory,
    ) {
    }

    public function handle(CreateInputData $inputData): void
    {
        $song = $this->factory->create(
            $inputData->title,
            $inputData->description,
            $inputData->releasedOn,
            $inputData->songTypeId,
            $inputData->orderNo,
            $inputData->lyricists,
            $inputData->composers,
            $inputData->arrangers,
        );

        $this->repository->save($song);
    }
}
