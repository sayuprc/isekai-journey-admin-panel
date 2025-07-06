<?php

declare(strict_types=1);

namespace User\Domain\Models;

interface UserRepositoryInterface
{
    public function findByEmail(Email $email): ?User;

    public function insert(User $user): void;
}
