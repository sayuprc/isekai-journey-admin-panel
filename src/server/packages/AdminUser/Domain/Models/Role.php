<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models;

enum Role: int
{
    case Privilege = 1;

    case General = 2;

    public function isPrivilege(): bool
    {
        return $this === self::Privilege;
    }
}
