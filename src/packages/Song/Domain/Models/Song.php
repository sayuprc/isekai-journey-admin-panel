<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use Song\Domain\Models\Archives\Archive;
use Song\Domain\Models\Creators\Arranger;
use Song\Domain\Models\Creators\Composer;
use Song\Domain\Models\Creators\Lyricist;
use SongType\Domain\Models\SongTypeId;
use Support\Domain\ValueObjects\OrderNo;

class Song
{
    /**
     * @param array<Lyricist> $lyricists
     * @param array<Composer> $composers
     * @param array<Arranger> $arrangers
     * @param array<Archive>  $archives
     */
    public function __construct(
        public readonly SongId $songId,
        public readonly Title $title,
        public readonly Description $description,
        public readonly SongTypeId $songTypeId,
        public readonly array $lyricists,
        public readonly array $composers,
        public readonly array $arrangers,
        public readonly array $archives,
        public readonly OrderNo $orderNo,
    ) {
    }
}
