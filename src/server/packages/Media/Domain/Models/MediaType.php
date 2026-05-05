<?php

declare(strict_types=1);

namespace Media\Domain\Models;

enum MediaType: int
{
    case Mv = 1;

    case OfficialAudio = 2;

    case SingingVideo = 3;

    case StreamArchive = 4;

    case ShortVideo = 5;

    case Article = 6;

    case SocialPost = 7;

    case OfficialPage = 8;

    case Other = 9;

    public function getName(): string
    {
        return match ($this) {
            self::Mv => 'MV',
            self::OfficialAudio => '公式音源',
            self::SingingVideo => '歌唱動画',
            self::StreamArchive => '配信アーカイブ',
            self::ShortVideo => 'ショート動画',
            self::Article => '記事',
            self::SocialPost => 'SNS投稿',
            self::OfficialPage => '公式ページ',
            self::Other => 'その他',
        };
    }

    public function equals(self $other): bool
    {
        return $this === $other;
    }
}
