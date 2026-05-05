<?php

declare(strict_types=1);

namespace Song\Domain\Models\Media;

use Media\Domain\Models\MediaId;
use Support\Domain\ValueObjects\OrderNo;

readonly class SongMediaLink
{
    public function __construct(
        public MediaId $mediaId,
        public SongMediaType $songMediaType,
        public OrderNo $orderNo,
    ) {
    }

    public static function reconstruct(string $mediaId, int $songMediaType, int $orderNo): self
    {
        return new self(
            MediaId::reconstruct($mediaId),
            SongMediaType::from($songMediaType),
            OrderNo::reconstruct($orderNo),
        );
    }

    /**
     * @return array{media_id: string, song_media_type: value-of<SongMediaType>, order_no: int}
     */
    public function toArray(): array
    {
        return [
            'media_id' => $this->mediaId->value,
            'song_media_type' => $this->songMediaType->value,
            'order_no' => $this->orderNo->value,
        ];
    }
}
