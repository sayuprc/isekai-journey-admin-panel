<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Infrastructures\Auth;

use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\HashedPassword;
use AdminUser\Domain\Models\Role;
use AdminUser\Infrastructures\AdminUserRepository;
use Auth\Infrastructures\Auth\AuthAdminUserRepository;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class AuthAdminUserRepositoryTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function find(): void
    {
        $createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $user = $this->createAdminUser($this->generateUuid(), 'user@example.com', Role::General, [], $createdAt);

        $this->app->make(AdminUserRepository::class)->register($user, HashedPassword::reconstruct('hashed-password'));

        $found = $this->getInstance()->find($user->adminUserId);

        $this->assertNotNull($found);
        $this->assertSame($user->adminUserId->value, $found->adminUserId->value);
        $this->assertSame('hashed-password', $found->hashedPassword->value);
    }

    #[Test]
    public function findNotFound(): void
    {
        $found = $this->getInstance()->find(AdminUserId::reconstruct($this->generateUuid()));

        $this->assertNull($found);
    }

    #[Test]
    public function findByEmail(): void
    {
        $createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $user = $this->createAdminUser($this->generateUuid(), 'user@example.com', Role::General, [], $createdAt);

        $this->app->make(AdminUserRepository::class)->register($user, HashedPassword::reconstruct('hashed-password'));

        $found = $this->getInstance()->findByEmail($user->email);

        $this->assertNotNull($found);
        $this->assertSame($user->adminUserId->value, $found->adminUserId->value);
        $this->assertSame('hashed-password', $found->hashedPassword->value);
    }

    #[Test]
    public function findByEmailNotFound(): void
    {
        $found = $this->getInstance()->findByEmail(Email::reconstruct('notfound@example.com'));

        $this->assertNull($found);
    }

    private function getInstance(): AuthAdminUserRepository
    {
        return $this->app->make(AuthAdminUserRepository::class);
    }
}
