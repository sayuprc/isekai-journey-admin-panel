<?php

declare(strict_types=1);

namespace Auth\Domain\Models;

use AdminUser\Domain\Models\AdminUserId;

readonly class AuthenticatableAdminUser
{
    public function __construct(
        public AdminUserId $adminUserId,
    ) {
    }

    public static function reconstruct(string $adminUserId): self
    {
        return new self(AdminUserId::reconstruct($adminUserId));
    }
}
