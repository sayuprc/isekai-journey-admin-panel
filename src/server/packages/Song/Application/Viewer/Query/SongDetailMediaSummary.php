<?php

declare(strict_types=1);

namespace Song\Application\Viewer\Query;

use DateTimeImmutable;
use Media\Domain\Models\MediaFormat;
use Media\Domain\Models\MediaType;

readonly class SongDetailMediaSummary
{
    public function __construct(
        public string $mediaId,
        public string $title,
        public MediaType $type,
        public MediaFormat $format,
        public DateTimeImmutable $publishedAt,
    ) {
    }
}
