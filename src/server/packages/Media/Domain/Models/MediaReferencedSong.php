<?php

declare(strict_types=1);

namespace Media\Domain\Models;

use Support\Domain\ValueObjects\OrderNo;

readonly class MediaReferencedSong
{
    public function __construct(
        public SongId $songId,
        public SongTitle $title,
        public OrderNo $songOrderNo,
        public OrderNo $mediaOrderNo,
    ) {
    }

    public static function reconstruct(
        string $songId,
        string $title,
        int $songOrderNo,
        int $mediaOrderNo,
    ): self {
        return new self(
            SongId::reconstruct($songId),
            SongTitle::reconstruct($title),
            OrderNo::reconstruct($songOrderNo),
            OrderNo::reconstruct($mediaOrderNo),
        );
    }
}
