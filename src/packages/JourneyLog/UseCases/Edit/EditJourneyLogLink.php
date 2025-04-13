<?php

declare(strict_types=1);

namespace JourneyLog\UseCases\Edit;

class EditJourneyLogLink
{
    /**
     * @param positive-int $orderNo
     */
    public function __construct(
        public readonly string $journeyLogLinkName,
        public readonly string $url,
        public readonly int $orderNo,
        public readonly string $journeyLogLinkTypeId,
    ) {
    }
}
