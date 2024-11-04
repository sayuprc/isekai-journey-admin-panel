<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Domain\Entities;

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
