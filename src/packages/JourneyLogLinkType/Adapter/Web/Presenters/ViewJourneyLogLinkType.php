<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Adapter\Web\Presenters;

class ViewJourneyLogLinkType
{
    public function __construct(
        public readonly string $journeyLogLinkTypeId,
        public readonly string $journeyLogLinkTypeName,
        public readonly int $orderNo,
    ) {
    }
}
