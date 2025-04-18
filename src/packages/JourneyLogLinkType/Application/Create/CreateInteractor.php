<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\Create;

use JourneyLogLinkType\Domain\Models\JourneyLogLinkType;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeName;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\UseCases\Create\CreateRequest;
use JourneyLogLinkType\UseCases\Create\CreateUseCaseInterface;
use Support\Domain\ValueObjects\OrderNo;
use Support\Uuid\UuidGeneratorInterface;

class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private readonly JourneyLogLinkTypeRepositoryInterface $repository,
        private readonly UuidGeneratorInterface $uuid,
    ) {
    }

    public function handle(CreateRequest $request): void
    {
        $journeyLogLinkType = new JourneyLogLinkType(
            new JourneyLogLinkTypeId($this->uuid->generate()),
            new JourneyLogLinkTypeName($request->journeyLogLinkTypeName),
            new OrderNo($request->orderNo),
        );

        $this->repository->insert($journeyLogLinkType);
    }
}
