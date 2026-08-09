<?php

declare(strict_types=1);

namespace Media\Application\Cli\UseCase\ImportYouTube;

use Media\Domain\Models\YouTubeChannel\YouTubeChannel;

readonly class ChannelImportResult
{
    private function __construct(
        public YouTubeChannel $channel,
        public int $importedCount,
        public bool $channelFound,
    ) {
    }

    public static function imported(YouTubeChannel $channel, int $importedCount): self
    {
        return new self($channel, $importedCount, true);
    }

    public static function channelNotFound(YouTubeChannel $channel): self
    {
        return new self($channel, 0, false);
    }
}
