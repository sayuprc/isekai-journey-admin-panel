<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use Song\Domain\Models\Creators\Arrangers;
use Song\Domain\Models\Creators\Composers;
use Song\Domain\Models\Creators\Lyricists;
use Support\Domain\ValueObjects\OrderNo;

readonly class Song
{
    public function __construct(
        public SongId $songId,
        public Title $title,
        public Description $description,
        public SongType $type,
        public ?SongAttribute $attribute,
        public OrderNo $orderNo,
        public bool $isDisplay,
        public Lyricists $lyricists,
        public Composers $composers,
        public Arrangers $arrangers,
    ) {
    }

    /**
     * @param list<array{creatorId: string, orderNo: int}> $lyricists
     * @param list<array{creatorId: string, orderNo: int}> $composers
     * @param list<array{creatorId: string, orderNo: int}> $arrangers
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
        // TODO デフォルト null をどうにかする
        ?int $attribute = null,
    ): self {
        return new self(
            SongId::reconstruct($songId),
            Title::reconstruct($title),
            Description::reconstruct($description),
            SongType::from($type),
            is_null($attribute) ? null : SongAttribute::from($attribute),
            OrderNo::reconstruct($orderNo),
            $isDisplay,
            Lyricists::reconstruct($lyricists),
            Composers::reconstruct($composers),
            Arrangers::reconstruct($arrangers),
        );
    }

    /**
     * @return array{song_id: string, title: string, description: string, type: value-of<SongType>, attribute: value-of<SongAttribute>|null, order_no: int, is_display: bool, lyricists: array<int, array{creator_id: string, order_no: int}>, composers: array<int, array{creator_id: string, order_no: int}>, arrangers: array<int, array{creator_id: string, order_no: int}>}
     */
    public function toArray(): array
    {
        return [
            'song_id' => $this->songId->value,
            'title' => $this->title->value,
            'description' => $this->description->value,
            'type' => $this->type->value,
            'attribute' => $this->attribute?->value,
            'order_no' => $this->orderNo->value,
            'is_display' => $this->isDisplay,
            'lyricists' => $this->lyricists->toArray(),
            'composers' => $this->composers->toArray(),
            'arrangers' => $this->arrangers->toArray(),
        ];
    }

    public function equals(self $other): bool
    {
        return $this->songId->equals($other->songId);
    }
}
