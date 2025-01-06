<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Port\UseCases\Create;

use App\Shared\Uuid\UuidGeneratorInterface;
use JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;
use JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeName;
use JourneyLogLinkType\Domain\Entities\OrderNo;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;

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
