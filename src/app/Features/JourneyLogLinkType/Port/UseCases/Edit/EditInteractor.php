<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Port\UseCases\Edit;

use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;
use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;
use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeName;
use App\Features\JourneyLogLinkType\Domain\Entities\OrderNo;
use App\Features\JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;

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

        $this->repository->createJourneyLogLinkType($journeyLogLinkType);
    }
}
