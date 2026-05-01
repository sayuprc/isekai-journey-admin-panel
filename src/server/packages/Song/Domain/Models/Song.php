<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use Song\Domain\Models\Creators\Arrangers;
use Song\Domain\Models\Creators\Composers;
use Song\Domain\Models\Creators\Lyricists;
use Song\Domain\Models\Tags\SongTagReferences;
use Support\Domain\ValueObjects\OrderNo;

readonly class Song
{
    public function __construct(
        public SongId $songId,
        public Title $title,
        public Description $description,
        public SongType $type,
        public bool $isDisplay,
        public OrderNo $orderNo,
        public Lyricists $lyricists,
        public Composers $composers,
        public Arrangers $arrangers,
        public SongTagReferences $tags,
    ) {
    }

    /**
     * @param list<array{creatorId: string, orderNo: int}> $lyricists
     * @param list<array{creatorId: string, orderNo: int}> $composers
     * @param list<array{creatorId: string, orderNo: int}> $arrangers
     * @param list<array{songTagId: string, orderNo: int}> $tags
     */
    public static function reconstruct(
        string $songId,
        string $title,
        string $description,
        int $type,
        int $orderNo,
        array $lyricists,
        array $composers,
        array $arrangers,
        bool $isDisplay,
        array $tags = [],
    ): self {
        return new self(
            SongId::reconstruct($songId),
            Title::reconstruct($title),
            Description::reconstruct($description),
            SongType::from($type),
            $isDisplay,
            OrderNo::reconstruct($orderNo),
            Lyricists::reconstruct($lyricists),
            Composers::reconstruct($composers),
            Arrangers::reconstruct($arrangers),
            SongTagReferences::reconstruct($tags),
        );
    }

    /**
     * @return array{song_id: string, title: string, description: string, type: value-of<SongType>, is_display: bool, order_no: int, lyricists: array<int, array{creator_id: string, order_no: int}>, composers: array<int, array{creator_id: string, order_no: int}>, arrangers: array<int, array{creator_id: string, order_no: int}>, tags: array<int, array{song_tag_id: string, order_no: int}>}
     */
    public function toArray(): array
    {
        return [
            'song_id' => $this->songId->value,
            'title' => $this->title->value,
            'description' => $this->description->value,
            'type' => $this->type->value,
            'is_display' => $this->isDisplay,
            'order_no' => $this->orderNo->value,
            'lyricists' => $this->lyricists->toArray(),
            'composers' => $this->composers->toArray(),
            'arrangers' => $this->arrangers->toArray(),
            'tags' => $this->tags->toArray(),
        ];
    }

    public function equals(self $other): bool
    {
        return $this->songId->equals($other->songId);
    }
}
