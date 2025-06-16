<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\Interactors;

use JourneyLogLinkType\Application\UseCase\Create\CreateInputData;
use JourneyLogLinkType\Application\UseCase\Create\CreateUseCaseInterface;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeFactoryInterface;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;

class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private readonly JourneyLogLinkTypeRepositoryInterface $repository,
        private readonly JourneyLogLinkTypeFactoryInterface $factory,
    ) {
    }

    public function handle(CreateInputData $inputData): void
    {
        $journeyLogLinkType = $this->factory->create($inputData->journeyLogLinkTypeName, $inputData->orderNo);

        $this->repository->insert($journeyLogLinkType);
    }
}
