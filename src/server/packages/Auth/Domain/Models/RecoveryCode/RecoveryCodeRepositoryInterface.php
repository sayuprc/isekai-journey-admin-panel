<?php

declare(strict_types=1);

namespace Auth\Domain\Models\RecoveryCode;

use AdminUser\Domain\Models\AdminUserId;

interface RecoveryCodeRepositoryInterface
{
    /**
     * @param list<RecoveryCode> $codes
     */
    public function saveMany(array $codes): void;

    public function deleteByAdminUserId(AdminUserId $adminUserId): void;
}
