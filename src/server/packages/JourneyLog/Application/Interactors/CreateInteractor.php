<?php

declare(strict_types=1);

namespace JourneyLog\Application\Interactors;

use JourneyLog\Application\UseCase\Create\CreateInputData;
use JourneyLog\Application\UseCase\Create\CreateUseCaseInterface;
use JourneyLog\Domain\Models\JourneyLogFactoryInterface;
use JourneyLog\Domain\Models\JourneyLogRepositoryInterface;

readonly class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private JourneyLogRepositoryInterface $repository,
        private JourneyLogFactoryInterface $factory,
    ) {
    }

    public function handle(CreateInputData $inputData): void
    {
        $journeyLog = $this->factory->create(
            $inputData->story,
            $inputData->fromOn,
            $inputData->toOn,
            $inputData->orderNo,
            $inputData->journeyLogLinks,
        );

        $this->repository->save($journeyLog);
    }
}
