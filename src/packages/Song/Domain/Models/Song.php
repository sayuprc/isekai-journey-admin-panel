<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use Song\Domain\Models\Creators\ArrangerId;
use Song\Domain\Models\Creators\ComposerId;
use Song\Domain\Models\Creators\LyricistId;
use SongType\Domain\Models\SongTypeId;
use Support\Domain\ValueObjects\OrderNo;

class Song
{
    /**
     * @param array<LyricistId> $lyricistIds
     * @param array<ComposerId> $composerIds
     * @param array<ArrangerId> $arrangerIds
     */
    public function __construct(
        public readonly SongId $songId,
        public readonly Title $title,
        public readonly Description $description,
        public readonly SongTypeId $songTypeId,
        public readonly array $lyricistIds,
        public readonly array $composerIds,
        public readonly array $arrangerIds,
        public readonly OrderNo $orderNo,
    ) {
    }
}
