<?php

declare(strict_types=1);

namespace Creator\Domain\Models;

readonly class Creator
{
    public function __construct(
        public CreatorId $creatorId,
        public CreatorName $creatorName,
    ) {
    }
}
