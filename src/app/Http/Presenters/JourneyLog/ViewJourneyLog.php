<?php

declare(strict_types=1);

namespace App\Http\Presenters\JourneyLog;

class ViewJourneyLog
{
    /**
     * @param array<array{journey_log_link_name: string, url: string, order_no: int, journey_log_link_type_id: string}> $journeyLogLinks
     */
    public function __construct(
        public readonly string $journeyLogId,
        public readonly string $story,
        public readonly ViewPeriod $fromOn,
        public readonly ViewPeriod $toOn,
        public readonly int $orderNo,
        public readonly array $journeyLogLinks,
    ) {
    }
}
