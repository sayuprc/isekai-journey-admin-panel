<?php

declare(strict_types=1);

namespace Song\Application\Interactors;

use Song\Application\UseCase\Delete\DeleteInputData;
use Song\Application\UseCase\Delete\DeleteUseCaseInterface;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;

readonly class DeleteInteractor implements DeleteUseCaseInterface
{
    public function __construct(private SongRepositoryInterface $repository)
    {
    }

    public function handle(DeleteInputData $inputData): void
    {
        SongId::create($inputData->songId)
            ->map(fn (SongId $songId) => $this->repository->delete($songId));
    }
}
