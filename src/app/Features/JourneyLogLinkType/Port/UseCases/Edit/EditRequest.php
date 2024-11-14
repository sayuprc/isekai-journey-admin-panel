<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Port\UseCases\Edit;

class EditRequest
{
    public function __construct(
        public readonly string $journeyLogLinkTypeId,
        public readonly string $journeyLogLinkTypeName,
        public readonly int $orderNo,
    ) {
    }
}
