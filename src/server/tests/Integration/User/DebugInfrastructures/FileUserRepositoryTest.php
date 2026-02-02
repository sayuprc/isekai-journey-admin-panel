<?php

declare(strict_types=1);

namespace Tests\Integration\User\DebugInfrastructures;

use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;
use User\DebugInfrastructures\FileUserRepository;
use User\Domain\Models\User;

class FileUserRepositoryTest extends TestCase
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

    private function store(User ...$users): void
    {
        array_map(
            fn (User $user) => $this->factory(FileUserRepository::class, $user->userId->value, $user),
            $users,
        );
    }

    private function getInstance(): FileUserRepository
    {
        return $this->app->make(FileUserRepository::class);
    }
}
