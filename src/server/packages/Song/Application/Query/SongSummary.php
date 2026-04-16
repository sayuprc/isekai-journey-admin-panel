<?php

declare(strict_types=1);

namespace Song\Application\Query;

use Song\Domain\Models\SongAttribute;
use Song\Domain\Models\SongType;

readonly class SongSummary
{
    public function __construct(
        public string $songId,
        public string $title,
        public SongType $type,
        public ?SongAttribute $attribute,
        public int $orderNo,
        public bool $isDisplay,
    ) {
    }
}
