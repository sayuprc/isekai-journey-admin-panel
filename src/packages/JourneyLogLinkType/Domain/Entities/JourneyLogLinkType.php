<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Domain\Entities;

class JourneyLogLinkType
{
    public function __construct(
        public readonly JourneyLogLinkTypeId $journeyLogLinkTypeId,
        public readonly JourneyLogLinkTypeName $journeyLogLinkTypeName,
        public readonly OrderNo $orderNo,
    ) {
    }
}
