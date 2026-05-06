<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models;

use DateTimeImmutable;

interface AdminUserRegistrationTokenRepositoryInterface
{
    public function find(AdminUserRegistrationTokenId $adminUserRegistrationTokenId): ?AdminUserRegistrationToken;

    /**
     * @return list<AdminUserRegistrationToken>
     */
    public function findByEmail(Email $email): array;

    public function save(AdminUserRegistrationToken $token): AdminUserRegistrationToken;

    public function markUsed(AdminUserRegistrationTokenId $adminUserRegistrationTokenId, DateTimeImmutable $usedAt): void;
}
