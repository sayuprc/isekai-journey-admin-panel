<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\Create;

use JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;
use JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeName;
use JourneyLogLinkType\Domain\Entities\OrderNo;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\UseCases\Create\CreateRequest;
use JourneyLogLinkType\UseCases\Create\CreateUseCaseInterface;
use Shared\Uuid\UuidGeneratorInterface;

class CreateInteractor implements CreateUseCaseInterface
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
