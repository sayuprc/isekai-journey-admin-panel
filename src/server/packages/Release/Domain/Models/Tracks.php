<?php

declare(strict_types=1);

namespace Release\Domain\Models;

use Song\Domain\Models\SongId;
use Support\Collection\ImmutableCollection;
use Support\Domain\Exceptions\DomainValidationException;
use Support\Domain\Validation\FieldErrors;
use Support\Domain\ValueObjects\OrderNo;

/**
 * @extends ImmutableCollection<int, Track>
 */
readonly class Tracks extends ImmutableCollection
{
    /**
     * @param list<array{songId: ?string, title: ?string, trackNo: int}> $items
     *
     * @throws DomainValidationException
     */
    public static function fromArray(array $items): self
    {
        $tracks = [];
        $seenTrackNos = [];

        foreach ($items as $item) {
            $errors = new FieldErrors();
            $songId = $errors->collect('songId', static fn (): ?SongId => is_null($item['songId']) ? null : new SongId($item['songId']));
            $title = $errors->collect('title', static fn (): ?TrackTitle => is_null($item['title']) ? null : new TrackTitle($item['title']));
            $trackNo = $errors->collect('trackNo', static fn (): OrderNo => new OrderNo($item['trackNo']));
            $errors->throwIfFailed();

            assert(! is_null($trackNo));

            $track = FieldErrors::single('media', static fn (): Track => Track::create($songId, $title, $trackNo));

            if (isset($seenTrackNos[$track->trackNo->value])) {
                throw new DomainValidationException([
                    'media' => ['同じ曲順を複数指定することはできません。'],
                ]);
            }

            $seenTrackNos[$track->trackNo->value] = true;
            $tracks[] = $track;
        }

        return new self($tracks);
    }

    /**
     * @param list<array{songId: ?string, title: ?string, trackNo: int}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(
            static fn (array $item): Track => Track::reconstruct(
                $item['songId'],
                $item['title'],
                $item['trackNo'],
            ),
            $items,
        ));
    }

    /**
     * @return list<array{song_id: ?string, title: ?string, track_no: int}>
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
