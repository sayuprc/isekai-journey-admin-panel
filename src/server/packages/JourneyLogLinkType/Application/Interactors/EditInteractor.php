<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\Interactors;

use JourneyLogLinkType\Application\UseCase\Edit\EditInputData;
use JourneyLogLinkType\Application\UseCase\Edit\EditUseCaseInterface;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeFactoryInterface;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeRepositoryInterface;

readonly class EditInteractor implements EditUseCaseInterface
{
    public function __construct(
        private JourneyLogLinkTypeRepositoryInterface $repository,
        private JourneyLogLinkTypeFactoryInterface $factory,
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
