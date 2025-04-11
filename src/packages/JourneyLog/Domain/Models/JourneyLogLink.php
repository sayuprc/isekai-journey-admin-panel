<?php

declare(strict_types=1);

namespace JourneyLog\Domain\Models;

use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use Support\Domain\ValueObjects\OrderNo;

class JourneyLogLink
{
    public function __construct(
        public readonly JourneyLogLinkId $journeyLogLinkId,
        public readonly JourneyLogLinkName $journeyLogLinkName,
        public readonly Url $url,
        public readonly OrderNo $orderNo,
        public readonly JourneyLogLinkTypeId $journeyLogLinkTypeId,
    ) {
    }
}
