<?php

declare(strict_types=1);

namespace Creator\Application\Interactors;

use Creator\Application\UseCase\Delete\DeleteInputData;
use Creator\Application\UseCase\Delete\DeleteUseCaseInterface;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorRepositoryInterface;

readonly class DeleteInteractor implements DeleteUseCaseInterface
{
    public function __construct(private CreatorRepositoryInterface $repository)
    {
    }

    public function handle(DeleteInputData $inputData): void
    {
        CreatorId::create($inputData->creatorId)
            ->map(fn (CreatorId $creatorId) => $this->repository->delete($creatorId));
    }
}
