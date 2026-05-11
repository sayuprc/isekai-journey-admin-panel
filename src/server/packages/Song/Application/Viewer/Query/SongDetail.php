<?php

declare(strict_types=1);

namespace Song\Application\Viewer\Query;

use Song\Domain\Models\SongType;

readonly class SongDetail
{
    /**
     * @param array<string>                 $lyricists
     * @param array<string>                 $composers
     * @param array<string>                 $arrangers
     * @param array<SongDetailMediaSummary> $media
     */
    public function __construct(
        public string $songId,
        public string $title,
        public string $description,
        public SongType $type,
        public array $lyricists,
        public array $composers,
        public array $arrangers,
        public array $media,
    ) {
    }
}
