<?php

declare(strict_types=1);

namespace JourneyLog\Application\Create;

use JourneyLog\Domain\Models\JourneyLogFactoryInterface;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLog\UseCases\Create\CreateRequest;
use JourneyLog\UseCases\Create\CreateUseCaseInterface;

class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private readonly JourneyLogRepositoryInterface $repository,
        private readonly JourneyLogFactoryInterface $factory,
    ) {
    }

    public function handle(CreateRequest $request): void
    {
        $journeyLog = $this->factory->create(
            $request->story,
            $request->fromOn,
            $request->toOn,
            $request->orderNo,
            $request->journeyLogLinks
        );

        $this->repository->insert($journeyLog);
    }
}
