<?php

declare(strict_types=1);

namespace Release\Domain\Models;

use Support\Domain\ValueObjects\OrderNo;

readonly class Medium
{
    public function __construct(
        public OrderNo $position,
        public MediumFormat $format,
        public Tracks $tracks,
    ) {
    }

    /**
     * @param list<array{songId: ?string, title: ?string, trackNo: int}> $tracks
     */
    public static function reconstruct(int $position, int $format, array $tracks): self
    {
        return new self(
            OrderNo::reconstruct($position),
            MediumFormat::from($format),
            Tracks::reconstruct($tracks),
        );
    }

    /**
     * @return array{position: int, format: value-of<MediumFormat>, tracks: list<array{song_id: ?string, title: ?string, track_no: int}>}
     */
    public function toArray(): array
    {
        return [
            'position' => $this->position->value,
            'format' => $this->format->value,
            'tracks' => $this->tracks->toArray(),
        ];
    }
}
