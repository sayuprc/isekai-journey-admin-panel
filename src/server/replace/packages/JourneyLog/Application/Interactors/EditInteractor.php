<?php

declare(strict_types=1);

namespace JourneyLog\Application\Interactors;

use JourneyLog\Application\UseCase\Edit\EditInputData;
use JourneyLog\Application\UseCase\Edit\EditUseCaseInterface;
use JourneyLog\Domain\Models\JourneyLogFactoryInterface;
use JourneyLog\Domain\Models\JourneyLogRepositoryInterface;

readonly class EditInteractor implements EditUseCaseInterface
{
    public function __construct(
        private JourneyLogRepositoryInterface $repository,
        private JourneyLogFactoryInterface $factory,
    ) {
    }

    public function handle(EditInputData $inputData): void
    {
        $journeyLog = $this->factory->createForUpdate(
            $inputData->journeyLogId,
            $inputData->story,
            $inputData->fromOn,
            $inputData->toOn,
            $inputData->orderNo,
            $inputData->journeyLogLinks,
        );

        $this->repository->update($journeyLog);
    }
}
