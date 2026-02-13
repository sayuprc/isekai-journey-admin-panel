<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\AdminUser;

use AdminUser\DebugInfrastructures\FileAdminUserRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class CreateCommandTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function canCreateUser(): void
    {
        $this->artisan('admin:create example@example.com plain')
            ->expectsOutput('管理ユーザーを作成しました')
            ->assertSuccessful();
    }

    #[Test]
    public function failureCreateUser(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(FileAdminUserRepository::class, $this->createUser($uuid, 'example@example.com', 'plain')->toArray());

        $this->artisan('admin:create example@example.com plain')
            ->expectsOutput('すでに使われているメールアドレスです "example@example.com"')
            ->assertFailed();
    }
}
