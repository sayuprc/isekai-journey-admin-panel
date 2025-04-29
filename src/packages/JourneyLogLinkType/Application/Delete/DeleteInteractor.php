<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\Delete;

use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\UseCases\Delete\DeleteInputData;
use JourneyLogLinkType\UseCases\Delete\DeleteUseCaseInterface;

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
