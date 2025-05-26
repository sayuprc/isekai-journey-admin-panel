<?php

declare(strict_types=1);

namespace JourneyLog\Domain\Models;

use JourneyLog\Domain\Models\JourneyLogLink\JourneyLogLink;
use Support\Domain\ValueObjects\OrderNo;

class JourneyLog
{
    /**
     * @param JourneyLogLink[] $journeyLogLinks
     */
    public function __construct(
        public readonly JourneyLogId $journeyLogId,
        public readonly Story $story,
        public readonly Period $period,
        public readonly OrderNo $orderNo,
        public readonly array $journeyLogLinks,
    ) {
    }
}
