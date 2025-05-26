<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Domain\Models;

interface JourneyLogLinkTypeFactoryInterface
{
    /**
     * @param positive-int $orderNo
     */
    public function create(string $journeyLogLinkTypeName, int $orderNo): JourneyLogLinkType;

    /**
     * @param positive-int $orderNo
     */
    public function reconstitute(string $journeyLogLinkTypeId, string $journeyLogLinkTypeName, int $orderNo): JourneyLogLinkType;
}
