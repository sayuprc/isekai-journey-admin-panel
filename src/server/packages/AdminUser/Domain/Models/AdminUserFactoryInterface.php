<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models;

interface AdminUserFactoryInterface
{
    public function create(
        AdminUserId $userId,
        Email $email,
        CreatedAt $createdAt,
        Role $role,
        Permissions $permissions,
    ): AdminUser;
}
