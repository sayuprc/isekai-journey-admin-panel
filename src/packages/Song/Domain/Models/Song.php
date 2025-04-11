<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use SongType\Domain\Models\SongTypeId;
use Support\Domain\ValueObjects\OrderNo;

class Song
{
    public function __construct(
        public readonly SongId $songId,
        public readonly Title $title,
        public readonly Description $description,
        public readonly SongTypeId $songTypeId,
        public readonly OrderNo $orderNo,
    ) {
    }
}
