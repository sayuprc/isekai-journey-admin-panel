<?php

declare(strict_types=1);

namespace Release\Application\Admin\Query;

readonly class ReleaseReferencedSong
{
    public function __construct(
        public int $mediumPosition,
        public int $trackNo,
        public ?string $songId,
        public string $title,
    ) {
    }
}
