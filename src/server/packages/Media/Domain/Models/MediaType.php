<?php

declare(strict_types=1);

namespace Media\Domain\Models;

enum MediaType: int
{
    case Video = 1;

    case Article = 2;

    case SocialPost = 3;

    case OfficialPage = 4;

    case Other = 99;

    public function getName(): string
    {
        return match ($this) {
            self::Video => '動画',
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
