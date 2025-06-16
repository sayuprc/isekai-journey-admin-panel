<?php

declare(strict_types=1);

namespace Creator\Application\Interactors;

use Creator\Application\UseCase\Delete\DeleteInputData;
use Creator\Application\UseCase\Delete\DeleteUseCaseInterface;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Repositories\CreatorRepositoryInterface;

class DeleteInteractor implements DeleteUseCaseInterface
{
    public function __construct(private readonly CreatorRepositoryInterface $repository)
    {
    }

    public function handle(DeleteInputData $inputData): void
    {
        $this->repository->delete(new CreatorId($inputData->creatorId));
    }
}
