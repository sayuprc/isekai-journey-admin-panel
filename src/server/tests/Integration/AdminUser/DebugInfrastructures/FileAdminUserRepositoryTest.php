<?php

declare(strict_types=1);

namespace Tests\Integration\AdminUser\DebugInfrastructures;

use AdminUser\DebugInfrastructures\FileAdminUserRepository;
use AdminUser\Domain\Models\AdminUser;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class FileAdminUserRepositoryTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function find(): void
    {
        $user = $this->createUser($this->generateUuid(), 'example@example.com', 'hashed');

        $this->store($user);

        $found = $this->getInstance()->find($user->userId);

        $this->assertNotNull($found);
        $this->assertEquals($user, $found);
    }

    #[Test]
    public function findByEmail(): void
    {
        $user = $this->createUser($this->generateUuid(), 'example@example.com', 'hashed');

        $this->store($user);

        $found = $this->getInstance()->findByEmail($user->email);

        $this->assertNotNull($found);
        $this->assertEquals($user, $found);
    }

    #[Test]
    public function save(): void
    {
        $user = $this->createUser($this->generateUuid(), 'example@example.com', 'hashed');

        $this->getInstance()->save($user);

        $found = $this->getInstance()->find($user->userId);

        $this->assertNotNull($found);
        $this->assertEquals($user, $found);
    }

    private function store(AdminUser ...$users): void
    {
        array_map(
            fn (AdminUser $user) => $this->factory(FileAdminUserRepository::class, $user->userId->value, $user),
            $users,
        );
    }

    private function getInstance(): FileAdminUserRepository
    {
        return $this->app->make(FileAdminUserRepository::class);
    }
}
