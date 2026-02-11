<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models;

interface AdminUserRepositoryInterface
{
    public function find(AdminUserId $userId): ?AdminUser;

    public function findByEmail(Email $email): ?AdminUser;

    public function save(AdminUser $user): AdminUser;
}
