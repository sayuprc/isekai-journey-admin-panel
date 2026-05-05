<?php

declare(strict_types=1);

namespace Media\Domain\Models;

use Song\Domain\Models\SongId;
use Song\Domain\Models\Title;
use Support\Domain\ValueObjects\OrderNo;

readonly class MediaReferencedSong
{
    public function __construct(
        public SongId $songId,
        public Title $title,
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
            Title::reconstruct($title),
            OrderNo::reconstruct($songOrderNo),
            OrderNo::reconstruct($mediaOrderNo),
        );
    }
}
