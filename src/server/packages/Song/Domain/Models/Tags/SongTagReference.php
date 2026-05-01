<?php

declare(strict_types=1);

namespace Song\Domain\Models\Tags;

use Song\Domain\Models\Tag\SongTagId;
use Support\Domain\ValueObjects\OrderNo;

readonly class SongTagReference
{
    public function __construct(
        public SongTagId $songTagId,
        public OrderNo $orderNo,
    ) {
    }

    public static function reconstruct(string $songTagId, int $orderNo): self
    {
        return new self(SongTagId::reconstruct($songTagId), OrderNo::reconstruct($orderNo));
    }

    /**
     * @return array{song_tag_id: string, order_no: int}
     */
    public function toArray(): array
    {
        return [
            'song_tag_id' => $this->songTagId->value,
            'order_no' => $this->orderNo->value,
        ];
    }
}
