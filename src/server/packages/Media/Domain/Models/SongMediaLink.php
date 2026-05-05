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
        public OrderNo $orderNo,
    ) {
    }

    public static function reconstruct(string $songId, string $mediaId, int $orderNo): self
    {
        return new self(
            SongId::reconstruct($songId),
            MediaId::reconstruct($mediaId),
            OrderNo::reconstruct($orderNo),
        );
    }

    /**
     * @return array{song_id: string, media_id: string, order_no: int}
     */
    public function toArray(): array
    {
        return [
            'song_id' => $this->songId->value,
            'media_id' => $this->mediaId->value,
            'order_no' => $this->orderNo->value,
        ];
    }
}
