<?php

declare(strict_types=1);

namespace Media\Domain\Models;

enum MediaFormat: int
{
    case Mv = 1;

    case AudioVideo = 2;

    case StreamArchive = 3;

    case ShortVideo = 4;

    case Teaser = 5;

    case LiveClip = 6;

    case Other = 99;

    public function getName(): string
    {
        return match ($this) {
            self::Mv => 'MV',
            self::AudioVideo => '音源動画',
            self::StreamArchive => '配信アーカイブ',
            self::ShortVideo => 'ショート動画',
            self::Teaser => 'ティザー',
            self::LiveClip => 'ライブクリップ',
            self::Other => 'その他',
        };
    }

    public function equals(self $other): bool
    {
        return $this === $other;
    }
}
