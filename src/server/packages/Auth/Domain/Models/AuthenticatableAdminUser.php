<?php

declare(strict_types=1);

namespace Auth\Domain\Models;

use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\HashedPassword;

readonly class AuthenticatableAdminUser
{
    public function __construct(
        public AdminUserId $userId,
        public HashedPassword $hashedPassword,
    ) {
    }

    public static function reconstruct(string $userId, string $hashedPassword): self
    {
        return new self(AdminUserId::reconstruct($userId), HashedPassword::reconstruct($hashedPassword));
    }
}
