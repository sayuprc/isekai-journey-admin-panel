<?php

declare(strict_types=1);

namespace Release\Domain\Models;

use DateType\ImmutableDate;

readonly class Release
{
    public function __construct(
        public ReleaseId $releaseId,
        public ReleaseTitle $title,
        public ReleaseType $type,
        public ReleaseDistributionType $distributionType,
        public ReleasedOn $releasedOn,
        public Description $description,
        public bool $isDisplay,
        public TrackEntries $trackEntries,
    ) {
    }

    /**
     * @param list<array{songId: string, trackNo: int}> $trackEntries
     */
    public static function reconstruct(
        string $releaseId,
        string $title,
        int $type,
        int $distributionType,
        ImmutableDate $releasedOn,
        string $description,
        bool $isDisplay,
        array $trackEntries,
    ): self {
        return new self(
            ReleaseId::reconstruct($releaseId),
            ReleaseTitle::reconstruct($title),
            ReleaseType::from($type),
            ReleaseDistributionType::from($distributionType),
            ReleasedOn::reconstruct($releasedOn),
            Description::reconstruct($description),
            $isDisplay,
            TrackEntries::reconstruct($trackEntries),
        );
    }

    /**
     * @return array{release_id: string, title: string, type: value-of<ReleaseType>, distribution_type: value-of<ReleaseDistributionType>, released_on: string, description: string, is_display: bool, track_entries: list<array{song_id: string, track_no: int}>}
     */
    public function toArray(): array
    {
        return [
            'release_id' => $this->releaseId->value,
            'title' => $this->title->value,
            'type' => $this->type->value,
            'distribution_type' => $this->distributionType->value,
            'released_on' => $this->releasedOn->value->format('Y-m-d'),
            'description' => $this->description->value,
            'is_display' => $this->isDisplay,
            'track_entries' => $this->trackEntries->toArray(),
        ];
    }

    public function equals(self $other): bool
    {
        return $this->releaseId->equals($other->releaseId);
    }
}
