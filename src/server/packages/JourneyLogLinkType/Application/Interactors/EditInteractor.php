<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\Interactors;

use JourneyLogLinkType\Application\UseCase\Edit\EditInputData;
use JourneyLogLinkType\Application\UseCase\Edit\EditUseCaseInterface;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeFactoryInterface;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;

class EditInteractor implements EditUseCaseInterface
{
    public function __construct(
        private readonly JourneyLogLinkTypeRepositoryInterface $repository,
        private readonly JourneyLogLinkTypeFactoryInterface $factory,
    ) {
    }

    public function handle(EditInputData $inputData): void
    {
        $journeyLogLinkType = $this->factory->reconstitute(
            $inputData->journeyLogLinkTypeId,
            $inputData->journeyLogLinkTypeName,
            $inputData->orderNo,
        );

        $this->repository->update($journeyLogLinkType);
    }
}
