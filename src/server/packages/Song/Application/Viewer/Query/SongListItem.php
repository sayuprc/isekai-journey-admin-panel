<?php

declare(strict_types=1);

namespace Song\Application\Viewer\Query;

use Song\Domain\Models\SongType;

readonly class SongListItem
{
    /**
     * @param array<string> $lyricists
     * @param array<string> $composers
     * @param array<string> $arrangers
     */
    public function __construct(
        public string $songId,
        public string $title,
        public SongType $type,
        public string $description,
        public array $lyricists,
        public array $composers,
        public array $arrangers,
        // public int $releaseCount,
        public int $mediaCount,
        public int $orderNo,
    ) {
    }
}
