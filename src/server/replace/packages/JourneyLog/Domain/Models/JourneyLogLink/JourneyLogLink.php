<?php

declare(strict_types=1);

namespace JourneyLog\Domain\Models\JourneyLogLink;

use JourneyLog\Domain\Models\Url;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use Support\Domain\ValueObjects\OrderNo;

readonly class JourneyLogLink
{
    public function __construct(
        public JourneyLogLinkId $journeyLogLinkId,
        public JourneyLogLinkName $journeyLogLinkName,
        public Url $url,
        public OrderNo $orderNo,
        public JourneyLogLinkTypeId $journeyLogLinkTypeId,
    ) {
    }
}
