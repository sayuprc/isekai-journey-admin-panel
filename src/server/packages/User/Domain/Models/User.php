<?php

declare(strict_types=1);

namespace User\Domain\Models;

readonly class User
{
    public function __construct(
        public UserId $userId,
        public Email $email,
        public HashedPassword $hashedPassword,
    ) {
    }
}
