<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Adapter\Web\Presenters;

class ViewJourneyLog
{
    public function __construct(
        public readonly string $journeyLogId,
        public readonly string $story,
        public readonly ViewPeriod $fromOn,
        public readonly ViewPeriod $toOn,
        public readonly int $orderNo,
    ) {
    }
}
