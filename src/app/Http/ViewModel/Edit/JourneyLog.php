<?php

declare(strict_types=1);

namespace App\Http\ViewModel\Edit;

use App\Http\ViewModel\ViewDate;

class JourneyLog
{
    public function __construct(
        public readonly string $journeyLogId,
        public readonly string $summary,
        public readonly string $story,
        public readonly ViewDate $fromOn,
        public readonly ViewDate $toOn,
        public readonly int $order_no
    ) {
    }
}
