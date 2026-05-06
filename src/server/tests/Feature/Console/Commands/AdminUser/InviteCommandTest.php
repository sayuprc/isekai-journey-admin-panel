<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\AdminUser;

use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;

class InviteCommandTest extends DatabaseTestCase
{
    #[Test]
    public function canIssueRegistrationToken(): void
    {
        $this->artisan('admin:invite テストユーザー invite@example.com')
            ->expectsOutput('管理ユーザー登録トークンを発行しました')
            ->expectsOutput('role: General')
            ->assertSuccessful();
    }

    #[Test]
    public function canIssuePrivilegeToken(): void
    {
        $this->artisan('admin:invite 特権ユーザー privilege@example.com --privilege')
            ->expectsOutput('管理ユーザー登録トークンを発行しました')
            ->expectsOutput('role: Privilege')
            ->assertSuccessful();
    }

    #[Test]
    public function failureWhenExpiresInMinutesIsInvalid(): void
    {
        $this->artisan('admin:invite テストユーザー invite@example.com --expires-in-minutes=0')
            ->expectsOutput('正の整数ではありません: 0')
            ->assertFailed();
    }
}
