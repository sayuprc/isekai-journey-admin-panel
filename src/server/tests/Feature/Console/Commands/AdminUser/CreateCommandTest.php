<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\AdminUser;

use AdminUser\DebugInfrastructures\FileAdminUserRepository;
use AdminUser\Domain\Models\Role;
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
        $this->artisan('admin:create テストユーザー example@example.com plain')
            ->expectsOutput('管理ユーザーを作成しました')
            ->assertSuccessful();
    }

    #[Test]
    public function canCreatePrivilegeUserWithPermissions(): void
    {
        $this->artisan('admin:create 特権ユーザー privilege@example.com plain --privilege read_admin_user write_admin_user')
            ->expectsOutput('管理ユーザーを作成しました')
            ->assertSuccessful();
    }

    #[Test]
    public function failureCreateUserWithInvalidPermission(): void
    {
        $this->artisan('admin:create テストユーザー invalid@example.com plain invalid_permission')
            ->expectsOutput('不正な権限です: invalid_permission')
            ->assertFailed();
    }

    #[Test]
    public function failureCreateUser(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(FileAdminUserRepository::class, $this->createUser($uuid, 'example@example.com', Role::General, [])->toArray());

        $this->artisan('admin:create テストユーザー example@example.com plain')
            ->expectsOutput('すでに使われているメールアドレスです "example@example.com"')
            ->assertFailed();
    }
}
