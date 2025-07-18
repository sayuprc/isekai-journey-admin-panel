<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use Song\Domain\Models\Creators\Arranger;
use Song\Domain\Models\Creators\Composer;
use Song\Domain\Models\Creators\Lyricist;
use SongType\Domain\Models\SongTypeId;

class Song
{
    /**
     * @param array<Lyricist> $lyricists
     * @param array<Composer> $composers
     * @param array<Arranger> $arrangers
     */
    public function __construct(
        public readonly SongId $songId,
        public readonly Title $title,
        public readonly Description $description,
        public readonly ReleasedOn $releasedOn,
        public readonly SongTypeId $songTypeId,
        public readonly array $lyricists,
        public readonly array $composers,
        public readonly array $arrangers,
    ) {
    }
}
