<?php

declare(strict_types=1);

namespace JourneyLog\Domain\Models;

use JourneyLog\Domain\Models\JourneyLogLink\JourneyLogLink;
use Support\Domain\ValueObjects\OrderNo;

readonly class JourneyLog
{
    /**
     * @param array<JourneyLogLink> $journeyLogLinks
     */
    public function __construct(
        public JourneyLogId $journeyLogId,
        public Story $story,
        public Period $period,
        public OrderNo $orderNo,
        public array $journeyLogLinks,
    ) {
    }
}
