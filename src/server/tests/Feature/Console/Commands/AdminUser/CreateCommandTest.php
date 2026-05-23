<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\AdminUser;

use AdminUser\Domain\Models\Role;
use AdminUser\Infrastructures\AdminUserRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class CreateCommandTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function canCreateUser(): void
    {
        $this->artisan('admin:create テストユーザー example@example.com')
            ->expectsOutput('管理ユーザーを作成しました')
            ->assertSuccessful();
    }

    #[Test]
    public function canCreatePrivilegeUserWithPermissions(): void
    {
        $this->artisan('admin:create 特権ユーザー privilege@example.com --privilege read_admin_user write_admin_user')
            ->expectsOutput('管理ユーザーを作成しました')
            ->assertSuccessful();
    }

    #[Test]
    public function failureCreateUserWithInvalidPermission(): void
    {
        $this->artisan('admin:create テストユーザー invalid@example.com invalid_permission')
            ->expectsOutput('不正な権限です: invalid_permission')
            ->assertFailed();
    }

    #[Test]
    public function failureCreateUser(): void
    {
        $uuid = $this->generateUuid();

        $this->app->make(AdminUserRepository::class)->register(
            $this->createAdminUser($uuid, 'example@example.com', Role::General, []),
        );

        $this->artisan('admin:create テストユーザー example@example.com')
            ->expectsOutput('すでに使われているメールアドレスです "example@example.com"')
            ->assertFailed();
    }
}
