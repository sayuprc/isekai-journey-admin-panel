<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Update;

/**
 * @phpstan-type TrackEntryInput array{songId: string, trackNo: int}
 */
readonly class UpdateInputData
{
    /**
     * @param list<TrackEntryInput> $trackEntries
     */
    public function __construct(
        public string $releaseId,
        public string $title,
        public int $typeValue,
        public int $distributionTypeValue,
        public string $releasedOn,
        public string $description,
        public bool $isDisplay,
        public array $trackEntries,
    ) {
    }
}
