<?php

declare(strict_types=1);

namespace Event\Domain\Models;

enum EventType: int
{
    case Live = 1;

    case Stream = 2;

    case MusicRelease = 3;

    case Other = 99;

    public function getName(): string
    {
        return match ($this) {
            self::Live => 'ライブ',
            self::Stream => '配信',
            self::MusicRelease => '楽曲公開',
            self::Other => 'その他',
        };
    }

    public function equals(self $other): bool
    {
        return $this === $other;
    }
}
