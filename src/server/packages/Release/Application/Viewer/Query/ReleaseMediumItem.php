<?php

declare(strict_types=1);

namespace Release\Application\Viewer\Query;

use Release\Domain\Models\MediumFormat;

readonly class ReleaseMediumItem
{
    /**
     * @param array<ReleaseTrackItem> $tracks
     */
    public function __construct(
        public int $position,
        public MediumFormat $format,
        public array $tracks,
    ) {
    }
}
