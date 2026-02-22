<?php

declare(strict_types=1);

namespace SongType\Domain\Models;

enum SongType: int
{
    case Original = 1;

    case Cover = 2;

    case Collaboration = 3;

    case Lineage = 4;

    case Derivative = 5;

    case Amplified = 6;

    public function getName(): string
    {
        return match ($this) {
            self::Original => 'オリジナル曲',
            self::Cover => 'カバー曲',
            self::Collaboration => 'コラボ曲',
            self::Lineage => '系譜曲',
            self::Derivative => '派生曲',
            self::Amplified => '拡声曲',
        };
    }

    public function equals(self $other): bool
    {
        return $this === $other;
    }
}
