<?php

declare(strict_types=1);

namespace JourneyLog\Port\UseCases\Edit;

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
