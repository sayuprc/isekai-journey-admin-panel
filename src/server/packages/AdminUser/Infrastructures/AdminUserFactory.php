<?php

declare(strict_types=1);

namespace AdminUser\Infrastructures;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserFactoryInterface;
use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\AdminUserName;
use AdminUser\Domain\Models\CreatedAt;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\Role;

readonly class AdminUserFactory implements AdminUserFactoryInterface
{
    public function create(
        AdminUserId $adminUserId,
        AdminUserName $name,
        Email $email,
        CreatedAt $createdAt,
        Role $role,
        Permissions $permissions,
    ): AdminUser {
        return new AdminUser(
            $adminUserId,
            $name,
            $email,
            $createdAt,
            $role,
            $permissions,
        );
    }
}
