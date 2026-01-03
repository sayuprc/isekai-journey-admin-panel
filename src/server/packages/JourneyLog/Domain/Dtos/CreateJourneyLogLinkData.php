<?php

declare(strict_types=1);

namespace JourneyLog\Domain\Dtos;

readonly class CreateJourneyLogLinkData
{
    /**
     * @param positive-int $orderNo
     */
    public function __construct(
        public string $journeyLogLinkName,
        public string $url,
        public int $orderNo,
        public string $journeyLogLinkTypeId,
    ) {
    }
}
