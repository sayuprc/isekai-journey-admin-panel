<?php

declare(strict_types=1);

namespace User\Domain\Models;

interface UserFactoryInterface
{
    public function create(string $email, string $plainPassword): User;
}
