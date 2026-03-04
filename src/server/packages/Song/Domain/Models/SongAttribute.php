<?php

declare(strict_types=1);

namespace Song\Domain\Models;

enum SongAttribute: int
{
    case Collaboration = 1;

    case Lineage = 2;

    case Derivative = 3;

    case Amplified = 4;

    case Isotope = 5;

    public function getName(): string
    {
        return match ($this) {
            self::Collaboration => 'コラボ',
            self::Lineage => '系譜曲',
            self::Derivative => '派生曲',
            self::Amplified => '拡声曲',
            self::Isotope => '音楽的同位体',
        };
    }
}
