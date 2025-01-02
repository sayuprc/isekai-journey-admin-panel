<?php

declare(strict_types=1);

namespace JourneyLogLinkType\UseCases\Edit;

use JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;
use JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeName;
use JourneyLogLinkType\Domain\Entities\OrderNo;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;

class EditInteractor
{
    public function __construct(private readonly JourneyLogLinkTypeRepositoryInterface $repository)
    {
    }

    public function handle(EditRequest $request): void
    {
        $journeyLogLinkType = new JourneyLogLinkType(
            new JourneyLogLinkTypeId($request->journeyLogLinkTypeId),
            new JourneyLogLinkTypeName($request->journeyLogLinkTypeName),
            new OrderNo($request->orderNo),
        );

        $this->repository->editJourneyLogLinkType($journeyLogLinkType);
    }
}
