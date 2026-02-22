<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models;

enum Role: int
{
    case Privilege = 1;

    case Console = 2;

    case General = 3;

    public function isPrivilege(): bool
    {
        return in_array($this, [self::Privilege, self::Console], true);
    }

    public function getName(): string
    {
        return match ($this) {
            self::Privilege => '特権',
            self::Console => 'コンソール',
            self::General => '一般',
        };
    }
}
