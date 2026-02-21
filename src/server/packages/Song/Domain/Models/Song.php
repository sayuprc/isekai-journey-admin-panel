<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use Song\Domain\Models\Creators\Arrangers;
use Song\Domain\Models\Creators\Composers;
use Song\Domain\Models\Creators\Lyricists;
use SongType\Domain\Models\SongType;
use Support\Domain\ValueObjects\OrderNo;

readonly class Song
{
    public function __construct(
        public SongId $songId,
        public Title $title,
        public Description $description,
        public SongType $songType,
        public OrderNo $orderNo,
        public Arrangers $arrangers,
        public Composers $composers,
        public Lyricists $lyricists,
    ) {
    }

    /**
     * @param list<array{creatorId: string, orderNo: int}> $arrangers
     * @param list<array{creatorId: string, orderNo: int}> $composers
     * @param list<array{creatorId: string, orderNo: int}> $lyricists
     */
    public static function reconstruct(
        string $songId,
        string $title,
        string $description,
        int $songType,
        int $orderNo,
        array $arrangers,
        array $composers,
        array $lyricists,
    ): self {
        return new self(
            SongId::reconstruct($songId),
            Title::reconstruct($title),
            Description::reconstruct($description),
            SongType::from($songType),
            OrderNo::reconstruct($orderNo),
            Arrangers::reconstruct($arrangers),
            Composers::reconstruct($composers),
            Lyricists::reconstruct($lyricists),
        );
    }

    /**
     * @return array{song_id: string, title: string, description: string, song_type: value-of<SongType>, order_no: int, arrangers: array<int, array{creator_id: string, order_no: int}>, composers: array<int, array{creator_id: string, order_no: int}>, lyricists: array<int, array{creator_id: string, order_no: int}>}
     */
    public function toArray(): array
    {
        return [
            'song_id' => $this->songId->value,
            'title' => $this->title->value,
            'description' => $this->description->value,
            'song_type' => $this->songType->value,
            'order_no' => $this->orderNo->value,
            'arrangers' => $this->arrangers->toArray(),
            'composers' => $this->composers->toArray(),
            'lyricists' => $this->lyricists->toArray(),
        ];
    }

    public function equals(self $other): bool
    {
        return $this->songId->equals($other->songId);
    }
}
