<?php

declare(strict_types=1);

namespace Song\Application\Viewer\Query;

use Song\Domain\Models\SongType;

readonly class SongListItem
{
    public function __construct(
        public string $songId,
        public string $title,
        public SongType $type,
        public string $description,
        public int $mediaCount,
        public int $orderNo,
    ) {
    }
}
