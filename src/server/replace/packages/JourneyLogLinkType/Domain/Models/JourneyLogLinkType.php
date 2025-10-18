<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Domain\Models;

use Support\Domain\ValueObjects\OrderNo;

readonly class JourneyLogLinkType
{
    public function __construct(
        public JourneyLogLinkTypeId $journeyLogLinkTypeId,
        public JourneyLogLinkTypeName $journeyLogLinkTypeName,
        public OrderNo $orderNo,
    ) {
    }
}
