<?php

declare(strict_types=1);

namespace Tests\Integration\AdminUser\DebugInfrastructures;

use AdminUser\DebugInfrastructures\FileAdminUserRepository;
use AdminUser\Domain\Models\HashedPassword;
use AdminUser\Domain\Models\Role;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class FileAdminUserRepositoryTest extends TestCase
{
    use EntityFactory;
    use EntityStore;
    use FileRepositoryTransaction;

    #[Test]
    public function allEmpty(): void
    {
        $result = $this->getInstance()->all();

        $this->assertCount(0, $result);
    }

    #[Test]
    public function allNonEmpty(): void
    {
        $createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $userA = $this->createAdminUser($this->generateUuid(), 'admin-a@example.com', Role::General, [], $createdAt);
        $userB = $this->createAdminUser($this->generateUuid(), 'admin-b@example.com', Role::Privilege, [], $createdAt);

        $this->storeAdminUsers($userA, $userB);

        $result = $this->getInstance()->all();

        $this->assertCount(2, $result);
        $this->assertSame($userA->adminUserId->value, $result[0]->adminUserId->value);
        $this->assertSame($userB->adminUserId->value, $result[1]->adminUserId->value);
    }

    #[Test]
    public function find(): void
    {
        $createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $user = $this->createAdminUser($this->generateUuid(), 'example@example.com', Role::General, [], $createdAt);

        $this->storeAdminUsers($user);

        $found = $this->getInstance()->find($user->adminUserId);

        $this->assertNotNull($found);
        $this->assertSame($user->adminUserId->value, $found->adminUserId->value);
        $this->assertSame($user->email->value, $found->email->value);
        $this->assertSame($createdAt->format('Y-m-d H:i:s'), $found->createdAt->value->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function findByEmail(): void
    {
        $createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $user = $this->createAdminUser($this->generateUuid(), 'example@example.com', Role::General, [], $createdAt);

        $this->storeAdminUsers($user);

        $found = $this->getInstance()->findByEmail($user->email);

        $this->assertNotNull($found);
        $this->assertSame($user->adminUserId->value, $found->adminUserId->value);
        $this->assertSame($user->email->value, $found->email->value);
        $this->assertSame($createdAt->format('Y-m-d H:i:s'), $found->createdAt->value->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function register(): void
    {
        $createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $user = $this->createAdminUser($this->generateUuid(), 'example@example.com', Role::General, [], $createdAt);

        $this->getInstance()->register($user, HashedPassword::reconstruct('hashed-password'));

        $found = $this->getInstance()->find($user->adminUserId);

        $this->assertNotNull($found);
        $this->assertSame($user->adminUserId->value, $found->adminUserId->value);
        $this->assertSame($user->email->value, $found->email->value);
        $this->assertSame($createdAt->format('Y-m-d H:i:s'), $found->createdAt->value->format('Y-m-d H:i:s'));
    }

    private function getInstance(): FileAdminUserRepository
    {
        return $this->app->make(FileAdminUserRepository::class);
    }
}
