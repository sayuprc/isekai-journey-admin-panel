<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\User;

use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;
use User\DebugInfrastructures\FileUserRepository;

class CreateCommandTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function createUser(): void
    {
        $this->artisan('user:create example@example.com plain')
            ->expectsOutput('ユーザーを作成しました')
            ->assertSuccessful();
    }

    #[Test]
    public function failureCreateUser(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(FileUserRepository::class, $uuid, $this->createUser($uuid, 'example@example.com', 'plain'));

        $this->artisan('user:create example@example.com plain')
            ->expectsOutput('User already exists: example@example.com')
            ->assertFailed();
    }
}
