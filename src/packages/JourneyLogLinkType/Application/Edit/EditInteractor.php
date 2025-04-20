<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\Edit;

use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeFactoryInterface;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\UseCases\Edit\EditRequest;
use JourneyLogLinkType\UseCases\Edit\EditUseCaseInterface;

class EditInteractor implements EditUseCaseInterface
{
    public function __construct(
        private readonly JourneyLogLinkTypeRepositoryInterface $repository,
        private readonly JourneyLogLinkTypeFactoryInterface $factory,
    ) {
    }

    public function handle(EditRequest $request): void
    {
        $journeyLogLinkType = $this->factory->reconstitute(
            $request->journeyLogLinkTypeId,
            $request->journeyLogLinkTypeName,
            $request->orderNo,
        );

        $this->repository->update($journeyLogLinkType);
    }
}
