<?php

declare(strict_types=1);

namespace Release\Domain\Models;

enum ReleaseDistributionType: int
{
    case Digital = 1;

    case Physical = 2;

    case Other = 99;

    public function getName(): string
    {
        return match ($this) {
            self::Digital => '配信',
            self::Physical => '物理',
            self::Other => 'その他',
        };
    }

    public function equals(self $other): bool
    {
        return $this === $other;
    }
}
