<?php

declare(strict_types=1);

namespace Media\Domain\Models;

use Song\Domain\Models\SongId;
use Support\Domain\ValueObjects\OrderNo;

readonly class SongMediaLink
{
    public function __construct(
        public SongId $songId,
        public MediaId $mediaId,
        public SongMediaType $songMediaType,
        public OrderNo $orderNo,
    ) {
    }

    public static function reconstruct(string $songId, string $mediaId, int $songMediaType, int $orderNo): self
    {
        return new self(
            SongId::reconstruct($songId),
            MediaId::reconstruct($mediaId),
            SongMediaType::from($songMediaType),
            OrderNo::reconstruct($orderNo),
        );
    }

    /**
     * @return array{song_id: string, media_id: string, song_media_type: value-of<SongMediaType>, order_no: int}
     */
    public function toArray(): array
    {
        return [
            'song_id' => $this->songId->value,
            'media_id' => $this->mediaId->value,
            'song_media_type' => $this->songMediaType->value,
            'order_no' => $this->orderNo->value,
        ];
    }
}
