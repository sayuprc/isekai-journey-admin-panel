<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models;

readonly class AdminUser
{
    public function __construct(
        public AdminUserId $userId,
        public Email $email,
        public HashedPassword $hashedPassword,
    ) {
    }
}
