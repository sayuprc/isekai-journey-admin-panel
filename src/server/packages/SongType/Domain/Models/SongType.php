<?php

declare(strict_types=1);

namespace SongType\Domain\Models;

enum SongType: int
{
    case Original = 1;

    case Cover = 2;

    public function getName(): string
    {
        return match ($this) {
            self::Original => 'オリジナル曲',
            self::Cover => 'カバー曲',
        };
    }

    public function equals(self $other): bool
    {
        return $this === $other;
    }
}
