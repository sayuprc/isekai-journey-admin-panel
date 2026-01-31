<?php

declare(strict_types=1);

namespace User\Infrastructures;

use User\Domain\Models\Email;
use User\Domain\Models\HashedPassword;
use User\Domain\Models\PlainPassword;
use User\Domain\Models\User;
use User\Domain\Models\UserFactoryInterface;
use User\Domain\Models\UserId;
use User\Domain\Services\HasherInterface;

readonly class UserFactory implements UserFactoryInterface
{
    public function __construct(private HasherInterface $hasher)
    {
    }

    public function create(UserId $userId, Email $email, PlainPassword $plainPassword): User
    {
        return new User(
            $userId,
            $email,
            // DB 値ではないが Result にする必要もないので reconstruct() を使う
            HashedPassword::reconstruct($this->hasher->hash($plainPassword->value)),
        );
    }
}
