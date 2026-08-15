<?php

declare(strict_types=1);

namespace Place\Domain\Models;

enum PlaceKind: int
{
    case Physical = 1;

    case Online = 2;

    public function getName(): string
    {
        return match ($this) {
            self::Physical => '会場',
            self::Online => '配信先',
        };
    }

    public function equals(self $other): bool
    {
        return $this === $other;
    }
}
