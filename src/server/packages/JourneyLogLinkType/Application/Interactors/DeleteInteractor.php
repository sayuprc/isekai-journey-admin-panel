<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\Interactors;

use JourneyLogLinkType\Application\UseCase\Delete\DeleteInputData;
use JourneyLogLinkType\Application\UseCase\Delete\DeleteUseCaseInterface;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeRepositoryInterface;

class DeleteInteractor implements DeleteUseCaseInterface
{
    public function __construct(private readonly JourneyLogLinkTypeRepositoryInterface $repository)
    {
    }

    public function handle(DeleteInputData $inputData): void
    {
        $journeyLogLinkTypeId = new JourneyLogLinkTypeId($inputData->journeyLogLinkTypeId);

        $this->repository->delete($journeyLogLinkTypeId);
    }
}
