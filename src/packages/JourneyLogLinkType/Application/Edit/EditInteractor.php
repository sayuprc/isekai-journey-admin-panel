<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\Edit;

use JourneyLogLinkType\Domain\Models\JourneyLogLinkType;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeName;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\UseCases\Edit\EditRequest;
use JourneyLogLinkType\UseCases\Edit\EditUseCaseInterface;
use Support\Domain\ValueObjects\OrderNo;

class EditInteractor implements EditUseCaseInterface
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
