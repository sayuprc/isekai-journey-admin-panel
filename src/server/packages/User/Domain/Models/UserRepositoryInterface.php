<?php

declare(strict_types=1);

namespace User\Domain\Models;

interface UserRepositoryInterface
{
    public function find(UserId $userId): ?User;

    public function findByEmail(Email $email): ?User;

    public function save(User $user): User;
}
