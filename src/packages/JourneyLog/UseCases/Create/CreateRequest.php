<?php

declare(strict_types=1);

namespace JourneyLog\UseCases\Create;

class CreateRequest
{
    /**
     * @param positive-int                $orderNo
     * @param array<CreateJourneyLogLink> $journeyLogLinks
     */
    public function __construct(
        public readonly string $story,
        public readonly string $fromOn,
        public readonly string $toOn,
        public readonly int $orderNo,
        public readonly array $journeyLogLinks,
    ) {
    }
}
