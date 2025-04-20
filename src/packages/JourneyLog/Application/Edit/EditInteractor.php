<?php

declare(strict_types=1);

namespace JourneyLog\Application\Edit;

use JourneyLog\Domain\Models\JourneyLogFactoryInterface;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLog\UseCases\Edit\EditRequest;
use JourneyLog\UseCases\Edit\EditUseCaseInterface;

class EditInteractor implements EditUseCaseInterface
{
    public function __construct(
        private readonly JourneyLogRepositoryInterface $repository,
        private readonly JourneyLogFactoryInterface $factory,
    ) {
    }

    public function handle(EditRequest $request): void
    {
        $journeyLog = $this->factory->createForUpdate(
            $request->journeyLogId,
            $request->story,
            $request->fromOn,
            $request->toOn,
            $request->orderNo,
            $request->journeyLogLinks,
        );

        $this->repository->update($journeyLog);
    }
}
