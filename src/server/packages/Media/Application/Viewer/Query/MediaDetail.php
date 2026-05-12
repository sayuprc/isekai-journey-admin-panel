<?php

declare(strict_types=1);

namespace Media\Application\Viewer\Query;

use DateTimeImmutable;
use Media\Domain\Models\MediaFormat;
use Media\Domain\Models\MediaType;

readonly class MediaDetail
{
    /**
     * @param array<MediaDetailSongSummary> $songs
     */
    public function __construct(
        public string $mediaId,
        public string $title,
        public string $url,
        public DateTimeImmutable $publishedAt,
        public MediaType $type,
        public MediaFormat $format,
        public array $songs,
    ) {
    }
}
