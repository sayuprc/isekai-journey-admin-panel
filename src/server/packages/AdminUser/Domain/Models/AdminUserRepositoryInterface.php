<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models;

interface AdminUserRepositoryInterface
{
    /**
     * @return array<AdminUser>
     */
    public function all(): array;

    public function find(AdminUserId $userId): ?AdminUser;

    public function findByEmail(Email $email): ?AdminUser;

    public function register(AdminUser $user, HashedPassword $hashedPassword): AdminUser;
}
