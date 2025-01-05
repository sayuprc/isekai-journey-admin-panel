<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Port\UseCases\Edit;

class EditJourneyLogLink
{
    public function __construct(
        public readonly string $journeyLogLinkName,
        public readonly string $url,
        public readonly int $orderNo,
        public readonly string $journeyLogLinkTypeId,
    ) {
    }
}
