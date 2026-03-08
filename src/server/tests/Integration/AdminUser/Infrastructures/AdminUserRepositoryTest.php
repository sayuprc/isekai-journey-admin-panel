<?php

declare(strict_types=1);

namespace Tests\Integration\AdminUser\Infrastructures;

use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\HashedPassword;
use AdminUser\Domain\Models\Role;
use AdminUser\Infrastructures\AdminUserRepository;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class AdminUserRepositoryTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function all(): void
    {
        $repository = $this->getInstance();

        $user1 = $this->createAdminUser($this->generateUuid(), 'user1@example.com', Role::General, [], new DateTimeImmutable('2026-01-01 00:00:00'));
        $user2 = $this->createAdminUser($this->generateUuid(), 'user2@example.com', Role::Privilege, [], new DateTimeImmutable('2026-01-02 00:00:00'));

        $repository->register($user1, HashedPassword::reconstruct('hashed-password'));
        $repository->register($user2, HashedPassword::reconstruct('hashed-password'));

        $users = $repository->all();

        $this->assertCount(2, $users);
        $this->assertEquals($user1, $users[0]);
        $this->assertEquals($user2, $users[1]);
    }

    #[Test]
    public function find(): void
    {
        $repository = $this->getInstance();

        $createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $user = $this->createAdminUser($this->generateUuid(), 'user@example.com', Role::General, [], $createdAt);

        $repository->register($user, HashedPassword::reconstruct('hashed-password'));

        $found = $repository->find($user->adminUserId);

        $this->assertNotNull($found);
        $this->assertEquals($user, $found);
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
        $repository = $this->getInstance();

        $createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $user = $this->createAdminUser($this->generateUuid(), 'user@example.com', Role::General, [], $createdAt);

        $repository->register($user, HashedPassword::reconstruct('hashed-password'));

        $found = $repository->findByEmail($user->email);

        $this->assertNotNull($found);
        $this->assertEquals($user, $found);
    }

    #[Test]
    public function findByEmailNotFound(): void
    {
        $repository = $this->getInstance();

        $createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $user = $this->createAdminUser($this->generateUuid(), 'user@example.com', Role::General, [], $createdAt);

        $found = $repository->findByEmail($user->email);

        $this->assertNull($found);
    }

    #[Test]
    public function registerWithPermissions(): void
    {
        $repository = $this->getInstance();

        $createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $user = $this->createAdminUser(
            $this->generateUuid(),
            'user@example.com',
            Role::General,
            ['read_creator', 'write_creator'],
            $createdAt,
        );

        $repository->register($user, HashedPassword::reconstruct('hashed-password'));

        $found = $repository->find($user->adminUserId);

        $this->assertNotNull($found);
        $this->assertEquals($user, $found);
    }

    private function getInstance(): AdminUserRepository
    {
        return $this->app->make(AdminUserRepository::class);
    }
}
