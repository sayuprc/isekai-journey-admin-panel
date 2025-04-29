<?php

declare(strict_types=1);

namespace JourneyLog\Application\Edit;

use JourneyLog\Domain\Models\JourneyLogFactoryInterface;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLog\UseCases\Edit\EditInputData;
use JourneyLog\UseCases\Edit\EditUseCaseInterface;

class EditInteractor implements EditUseCaseInterface
{
    public function __construct(
        private readonly JourneyLogRepositoryInterface $repository,
        private readonly JourneyLogFactoryInterface $factory,
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
