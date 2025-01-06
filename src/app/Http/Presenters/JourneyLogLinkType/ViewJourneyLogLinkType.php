<?php

declare(strict_types=1);

namespace App\Http\Presenters\JourneyLogLinkType;

class ViewJourneyLogLinkType
{
    public function __construct(
        public readonly string $journeyLogLinkTypeId,
        public readonly string $journeyLogLinkTypeName,
        public readonly int $orderNo,
    ) {
    }
}
