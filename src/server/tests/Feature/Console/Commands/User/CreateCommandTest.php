<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\User;

use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;
use User\DebugInfrastructures\FileUserRepository;
use User\Domain\Models\Email;
use User\Domain\Models\HashedPassword;
use User\Domain\Models\User;
use User\Domain\Models\UserId;

class CreateCommandTest extends TestCase
{
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

        $this->factory(
            FileUserRepository::class,
            $uuid,
            new User(new UserId($uuid), new Email('example@example.com'), new HashedPassword('plain'))
        );

        $this->artisan('user:create example@example.com plain')
            ->expectsOutput('User already exists: example@example.com')
            ->assertFailed();
    }
}
