<?php

declare(strict_types=1);

namespace Media\Application\Cli\Query;

readonly class YouTubeUploadedVideo
{
    public function __construct(
        public string $videoId,
        public string $title,
        public string $publishedAt,
    ) {
    }
}
