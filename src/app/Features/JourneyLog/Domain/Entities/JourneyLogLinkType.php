<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Domain\Entities;

class JourneyLogLinkType
{
    public function __construct(
        public readonly JourneyLogLinkTypeId $journeyLogLinkTypeId,
        public readonly JourneyLogLinkTypeName $journeyLogLinkTypeName,
        public readonly OrderNo $orderNo,
    ) {
    }
}
