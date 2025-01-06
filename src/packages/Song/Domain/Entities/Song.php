<?php

declare(strict_types=1);

namespace Song\Domain\Entities;

class Song
{
    /**
     * @param SongLink[] $songLinks
     */
    public function __construct(
        public readonly SongId $songId,
        public readonly Title $title,
        public readonly Description $description,
        public readonly ReleasedOn $releasedOn,
        public readonly SongTypeId $songTypeId,
        public readonly OrderNo $orderNo,
        public readonly array $songLinks,
    ) {
    }
}
