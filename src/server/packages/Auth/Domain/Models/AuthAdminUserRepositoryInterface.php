<?php

declare(strict_types=1);

namespace Auth\Domain\Models;

use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\Email;

interface AuthAdminUserRepositoryInterface
{
    public function find(AdminUserId $adminUserId): ?AuthenticatableAdminUser;

    public function findByEmail(Email $email): ?AuthenticatableAdminUser;
}
