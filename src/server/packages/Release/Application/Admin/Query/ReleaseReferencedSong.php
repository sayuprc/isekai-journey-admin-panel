<?php

declare(strict_types=1);

namespace Release\Application\Admin\Query;

readonly class ReleaseReferencedSong
{
    public function __construct(
        public string $songId,
        public string $title,
        public int $trackNo,
    ) {
    }
}
