<?php

declare(strict_types=1);

namespace SongType\Application\Interactors;

use SongType\Application\UseCase\Delete\DeleteInputData;
use SongType\Application\UseCase\Delete\DeleteUseCaseInterface;
use SongType\Domain\Models\SongTypeId;
use SongType\Domain\Models\SongTypeRepositoryInterface;

class DeleteInteractor implements DeleteUseCaseInterface
{
    public function __construct(private readonly SongTypeRepositoryInterface $repository)
    {
    }

    public function handle(DeleteInputData $inputData): void
    {
        $this->repository->delete(new SongTypeId($inputData->songTypeId));
    }
}
