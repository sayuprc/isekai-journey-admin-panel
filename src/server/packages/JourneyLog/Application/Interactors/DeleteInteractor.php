<?php

declare(strict_types=1);

namespace JourneyLog\Application\Interactors;

use JourneyLog\Application\UseCase\Delete\DeleteInputData;
use JourneyLog\Application\UseCase\Delete\DeleteUseCaseInterface;
use JourneyLog\Domain\Models\JourneyLogId;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;

class DeleteInteractor implements DeleteUseCaseInterface
{
    public function __construct(private readonly JourneyLogRepositoryInterface $repository)
    {
    }

    public function handle(DeleteInputData $inputData): void
    {
        $this->repository->delete(new JourneyLogId($inputData->journeyLogId));
    }
}
