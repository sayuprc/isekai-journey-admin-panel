<?php

declare(strict_types=1);

namespace Song\Domain\Models\Media;

use Media\Domain\Models\MediaId;
use Support\Domain\ValueObjects\OrderNo;

readonly class SongMediaLink
{
    public function __construct(
        public MediaId $mediaId,
        public OrderNo $orderNo,
    ) {
    }

    public static function reconstruct(string $mediaId, int $orderNo): self
    {
        return new self(
            MediaId::reconstruct($mediaId),
            OrderNo::reconstruct($orderNo),
        );
    }

    /**
     * @return array{media_id: string, order_no: int}
     */
    public function toArray(): array
    {
        return [
            'media_id' => $this->mediaId->value,
            'order_no' => $this->orderNo->value,
        ];
    }
}
