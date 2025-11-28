<?php

declare(strict_types=1);

namespace Performer\Domain\Models;

use Support\Domain\ValueObjects\OrderNo;

readonly class Performer
{
    public function __construct(
        public PerformerId $performerId,
        public PerformerName $performerName,
        public OrderNo $orderNo,
    ) {
    }
}
