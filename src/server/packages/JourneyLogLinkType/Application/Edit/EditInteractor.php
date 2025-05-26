<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\Edit;

use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeFactoryInterface;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\UseCases\Edit\EditInputData;
use JourneyLogLinkType\UseCases\Edit\EditUseCaseInterface;

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
