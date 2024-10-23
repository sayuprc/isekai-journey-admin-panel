<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Port\UseCases\Create;

class CreateRequest
{
    public function __construct(
        public readonly string $summary,
        public readonly string $story,
        public readonly string $fromOn,
        public readonly string $toOn,
        public readonly int $orderNo,
    ) {
    }
}
