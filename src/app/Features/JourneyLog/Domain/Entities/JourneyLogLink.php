<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Domain\Entities;

use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;

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
