<?php

declare(strict_types=1);

namespace Creator\Domain\Models;

class Creator
{
    public function __construct(
        public readonly CreatorId $creatorId,
        public readonly CreatorName $creatorName
    ) {
    }
}
