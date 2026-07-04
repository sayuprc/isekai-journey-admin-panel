<?php

declare(strict_types=1);

namespace Release\Application\Viewer\Query;

readonly class ReleaseListItem
{
    /**
     * @param array<ReleaseMediumItem> $media
     */
    public function __construct(
        public string $releaseId,
        public string $name,
        public string $releasedOn,
        public string $description,
        public ?string $jacketArtUrl,
        public array $media,
    ) {
    }
}
