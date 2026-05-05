<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use Media\Domain\Models\Media;
use Media\Domain\Models\SongMediaType;
use Support\Domain\ValueObjects\OrderNo;

readonly class SongAttachedMedia
{
    public function __construct(
        public Media $media,
        public SongMediaType $songMediaType,
        public OrderNo $orderNo,
    ) {
    }

    public static function reconstruct(
        string $mediaId,
        string $title,
        string $url,
        int $type,
        bool $isDisplay,
        int $songMediaType,
        int $orderNo,
    ): self {
        return new self(
            Media::reconstruct($mediaId, $title, $url, $type, $isDisplay),
            SongMediaType::from($songMediaType),
            OrderNo::reconstruct($orderNo),
        );
    }

    /**
     * @return array{media_id: string, title: string, url: string, type: int, is_display: bool, song_media_type: int, order_no: int}
     */
    public function toArray(): array
    {
        return [
            ...$this->media->toArray(),
            'song_media_type' => $this->songMediaType->value,
            'order_no' => $this->orderNo->value,
        ];
    }
}
