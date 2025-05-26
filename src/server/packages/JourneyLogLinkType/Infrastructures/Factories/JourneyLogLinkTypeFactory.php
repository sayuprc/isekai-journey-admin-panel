<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Infrastructures\Factories;

use JourneyLogLinkType\Domain\Models\JourneyLogLinkType;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeFactoryInterface;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeName;
use Support\Domain\ValueObjects\OrderNo;
use Support\Uuid\UuidGeneratorInterface;

class JourneyLogLinkTypeFactory implements JourneyLogLinkTypeFactoryInterface
{
    public function __construct(private readonly UuidGeneratorInterface $uuid)
    {
    }

    /**
     * @param positive-int $orderNo
     */
    public function create(string $journeyLogLinkTypeName, int $orderNo): JourneyLogLinkType
    {
        return new JourneyLogLinkType(
            new JourneyLogLinkTypeId($this->uuid->generate()),
            new JourneyLogLinkTypeName($journeyLogLinkTypeName),
            new OrderNo($orderNo)
        );
    }

    /**
     * @param positive-int $orderNo
     */
    public function reconstitute(string $journeyLogLinkTypeId, string $journeyLogLinkTypeName, int $orderNo): JourneyLogLinkType
    {
        return new JourneyLogLinkType(
            new JourneyLogLinkTypeId($journeyLogLinkTypeId),
            new JourneyLogLinkTypeName($journeyLogLinkTypeName),
            new OrderNo($orderNo)
        );
    }
}
