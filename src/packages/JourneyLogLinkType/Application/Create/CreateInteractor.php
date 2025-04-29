<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\Create;

use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeFactoryInterface;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\UseCases\Create\CreateInputData;
use JourneyLogLinkType\UseCases\Create\CreateUseCaseInterface;

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
