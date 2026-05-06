<?php

declare(strict_types=1);

namespace Tests\Integration\AdminUser\Infrastructures;

use AdminUser\Domain\Models\AdminUserRegistrationTokenId;
use AdminUser\Domain\Models\Role;
use AdminUser\Infrastructures\AdminUserRegistrationTokenRepository;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class AdminUserRegistrationTokenRepositoryTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function saveAndFind(): void
    {
        $repository = $this->getInstance();

        $token = $this->createAdminUserRegistrationToken(
            $this->generateUuid(),
            'テストユーザー',
            'invite@example.com',
            Role::General,
            'hashed-token',
            new DateTimeImmutable('2026-01-01 01:00:00'),
            null,
            new DateTimeImmutable('2026-01-01 00:00:00'),
        );

        $repository->save($token);

        $found = $repository->find($token->adminUserRegistrationTokenId);

        $this->assertNotNull($found);
        $this->assertEquals($token, $found);
    }

    #[Test]
    public function findNotFound(): void
    {
        $found = $this->getInstance()->find(AdminUserRegistrationTokenId::reconstruct($this->generateUuid()));

        $this->assertNull($found);
    }

    private function getInstance(): AdminUserRegistrationTokenRepository
    {
        return $this->app->make(AdminUserRegistrationTokenRepository::class);
    }
}
