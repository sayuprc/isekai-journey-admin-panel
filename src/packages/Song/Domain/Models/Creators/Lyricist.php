<?php

declare(strict_types=1);

namespace Song\Domain\Models\Creators;

use Creator\Domain\Models\CreatorId;
use Support\Domain\ValueObjects\OrderNo;

class Lyricist
{
    public function __construct(
        public readonly CreatorId $creatorId,
        public readonly OrderNo $orderNo,
    ) {
    }
}
