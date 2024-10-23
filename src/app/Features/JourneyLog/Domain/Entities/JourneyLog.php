<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Domain\Entities;

class JourneyLog
{
    public function __construct(
        public readonly JourneyLogId $journeyLogId,
        public readonly Summary $summary,
        public readonly Story $story,
        public readonly EventDate $eventDate,
        public readonly OrderNo $orderNo,
    ) {
    }
}
