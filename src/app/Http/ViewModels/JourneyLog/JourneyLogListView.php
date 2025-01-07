<?php

declare(strict_types=1);

namespace App\Http\ViewModels\JourneyLog;

class JourneyLogListView
{
    public function __construct(
        public readonly string $journeyLogId,
        public readonly string $story,
        public readonly string $period,
        public readonly int $orderNo,
    ) {
    }
}
