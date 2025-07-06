<?php

declare(strict_types=1);

namespace User\Domain\Models;

class User
{
    public function __construct(
        public readonly UserId $userId,
        public readonly Email $email,
        public readonly HashedPassword $hashedPassword,
    ) {
    }
}
