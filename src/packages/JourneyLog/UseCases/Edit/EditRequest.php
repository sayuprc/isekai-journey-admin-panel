<?php

declare(strict_types=1);

namespace JourneyLog\UseCases\Edit;

class EditRequest
{
    /**
     * @param array<EditJourneyLogLink> $journeyLogLinks
     */
    public function __construct(
        public readonly string $journeyLogId,
        public readonly string $story,
        public readonly string $fromOn,
        public readonly string $toOn,
        public readonly int $orderNo,
        public readonly array $journeyLogLinks,
    ) {
    }
}
