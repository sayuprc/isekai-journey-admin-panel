<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Port\UseCases\Edit;

class EditRequest
{
    public function __construct(
        public readonly string $journeyLodId,
        public readonly string $story,
        public readonly string $fromOn,
        public readonly string $toOn,
        public readonly int $orderNo,
    ) {
    }
}
