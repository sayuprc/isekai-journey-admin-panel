<?php

declare(strict_types=1);

namespace Auth\Domain\Models;

use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\HashedPassword;

readonly class AuthenticatableAdminUser
{
    public function __construct(
        public AdminUserId $adminUserId,
        public HashedPassword $hashedPassword,
    ) {
    }

    public static function reconstruct(string $adminUserId, string $hashedPassword): self
    {
        return new self(AdminUserId::reconstruct($adminUserId), HashedPassword::reconstruct($hashedPassword));
    }
}
