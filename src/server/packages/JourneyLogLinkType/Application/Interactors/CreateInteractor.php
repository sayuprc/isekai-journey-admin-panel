<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\Interactors;

use JourneyLogLinkType\Application\UseCase\Create\CreateInputData;
use JourneyLogLinkType\Application\UseCase\Create\CreateUseCaseInterface;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeFactoryInterface;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeRepositoryInterface;

readonly class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private JourneyLogLinkTypeRepositoryInterface $repository,
        private JourneyLogLinkTypeFactoryInterface $factory,
    ) {
    }

    public function handle(CreateInputData $inputData): void
    {
        $journeyLogLinkType = $this->factory->create($inputData->journeyLogLinkTypeName, $inputData->orderNo);

        $this->repository->save($journeyLogLinkType);
    }
}
