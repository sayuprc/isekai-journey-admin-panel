<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use Song\Domain\Models\Creators\Arranger;
use Song\Domain\Models\Creators\Composer;
use Song\Domain\Models\Creators\Lyricist;
use SongType\Domain\Models\SongTypeId;
use Support\Domain\ValueObjects\OrderNo;

readonly class Song
{
    /**
     * @param array<Lyricist> $lyricists
     * @param array<Composer> $composers
     * @param array<Arranger> $arrangers
     */
    public function __construct(
        public SongId $songId,
        public Title $title,
        public Description $description,
        public ReleasedOn $releasedOn,
        public SongTypeId $songTypeId,
        public OrderNo $orderNo,
        public array $lyricists,
        public array $composers,
        public array $arrangers,
    ) {
    }
}
