<?php

declare(strict_types=1);

namespace Release\Domain\Models;

use Support\Collection\ImmutableCollection;

/**
 * @extends ImmutableCollection<int, TrackEntry>
 */
readonly class TrackEntries extends ImmutableCollection
{
    /**
     * @param list<array{songId: string, trackNo: int}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(
            static fn (array $item): TrackEntry => TrackEntry::reconstruct(
                $item['songId'],
                $item['trackNo'],
            ),
            $items,
        ));
    }

    /**
     * @return list<array{song_id: string, track_no: int}>
     */
    public function toArray(): array
    {
        $items = [];

        foreach ($this->toGeneric() as $item) {
            $items[] = $item->toArray();
        }

        return $items;
    }
}
