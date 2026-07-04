<?php

declare(strict_types=1);

namespace Release\Domain\Models;

use Song\Domain\Models\SongId;
use Support\Domain\ValueObjects\OrderNo;

readonly class Track
{
    public function __construct(
        public SongId $songId,
        public OrderNo $trackNo,
    ) {
    }

    public static function reconstruct(string $songId, int $trackNo): self
    {
        return new self(
            SongId::reconstruct($songId),
            OrderNo::reconstruct($trackNo),
        );
    }

    /**
     * @return array{song_id: string, track_no: int}
     */
    public function toArray(): array
    {
        return [
            'song_id' => $this->songId->value,
            'track_no' => $this->trackNo->value,
        ];
    }
}
