<?php

declare(strict_types=1);

namespace User\Domain\Models;

interface UserFactoryInterface
{
    public function create(UserId $userId, Email $email, PlainPassword $plainPassword): User;
}
