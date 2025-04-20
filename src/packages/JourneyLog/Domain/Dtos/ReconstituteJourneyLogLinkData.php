<?php

declare(strict_types=1);

namespace JourneyLog\Domain\Dtos;

class ReconstituteJourneyLogLinkData
{
    /**
     * @param positive-int $orderNo
     */
    public function __construct(
        public readonly string $journeyLogLinkId,
        public readonly string $journeyLogLinkName,
        public readonly string $url,
        public readonly int $orderNo,
        public readonly string $journeyLogLinkTypeId
    ) {
    }
}
