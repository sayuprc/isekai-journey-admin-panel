<?php

declare(strict_types=1);

namespace Auth\Domain\Models\AdminRegistrationToken;

use AdminUser\Domain\Models\Email;

interface AdminRegistrationTokenRepositoryInterface
{
    /**
     * @return list<AdminRegistrationToken>
     */
    public function findAvailableByEmail(Email $email): array;

    public function save(AdminRegistrationToken $adminRegistrationToken): AdminRegistrationToken;
}
