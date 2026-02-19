<?php

declare(strict_types=1);

namespace AdminUser\Infrastructures;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserFactoryInterface;
use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\HashedPassword;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\PlainPassword;
use AdminUser\Domain\Models\Role;
use AdminUser\Domain\Services\HasherInterface;

readonly class AdminUserFactory implements AdminUserFactoryInterface
{
    public function __construct(private HasherInterface $hasher)
    {
    }

    public function create(
        AdminUserId $userId,
        Email $email,
        PlainPassword $plainPassword,
        Role $role,
        Permissions $permissions,
    ): AdminUser {
        return new AdminUser(
            $userId,
            $email,
            // DB 値ではないが Result にする必要もないので reconstruct() を使う
            HashedPassword::reconstruct($this->hasher->hash($plainPassword->value)),
            $role,
            $permissions,
        );
    }
}
