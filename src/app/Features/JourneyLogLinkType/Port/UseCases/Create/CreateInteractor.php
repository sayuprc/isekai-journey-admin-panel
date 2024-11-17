<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Port\UseCases\Create;

use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;
use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;
use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeName;
use App\Features\JourneyLogLinkType\Domain\Entities\OrderNo;
use App\Features\JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use App\Shared\Uuid\UuidGeneratorInterface;

class CreateInteractor
{
    public function __construct(
        private readonly JourneyLogLinkTypeRepositoryInterface $repository,
        private readonly UuidGeneratorInterface $generator,
    ) {
    }

    public function handle(CreateRequest $request): void
    {
        $journeyLogLinkType = new JourneyLogLinkType(
            new JourneyLogLinkTypeId($this->generator->generate()),
            new JourneyLogLinkTypeName($request->journeyLogLinkTypeName),
            new OrderNo($request->orderNo),
        );

        $this->repository->createJourneyLogLinkType($journeyLogLinkType);
    }
}
