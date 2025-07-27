<?php

declare(strict_types=1);

namespace Song\Domain\Models\Creators;

use Creator\Domain\Models\CreatorId;
use Support\Domain\ValueObjects\OrderNo;

readonly class Arranger
{
    public function __construct(
        public CreatorId $creatorId,
        public OrderNo $orderNo,
    ) {
    }
}
