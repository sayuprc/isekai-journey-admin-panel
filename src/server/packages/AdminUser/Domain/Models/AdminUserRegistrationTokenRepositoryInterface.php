<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models;

interface AdminUserRegistrationTokenRepositoryInterface
{
    public function find(AdminUserRegistrationTokenId $adminUserRegistrationTokenId): ?AdminUserRegistrationToken;

    public function save(AdminUserRegistrationToken $token): AdminUserRegistrationToken;
}
