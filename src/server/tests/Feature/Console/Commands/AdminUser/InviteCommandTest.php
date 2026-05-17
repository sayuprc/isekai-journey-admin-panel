<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\AdminUser;

use App\Models\AdminUser\AdminUserInvitation;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;

class InviteCommandTest extends DatabaseTestCase
{
    #[Test]
    public function canIssueInvitationToken(): void
    {
        $this->artisan('admin:invite')
            ->expectsOutputToContain('token:')
            ->expectsOutputToContain('expires_at:')
            ->assertSuccessful();

        $this->assertSame(1, AdminUserInvitation::query()->count());
    }

    #[Test]
    public function canIssuePrivilegeInvitationWithPermissions(): void
    {
        $this->artisan('admin:invite --privilege --expires-in-hours=48 read_admin_user write_admin_user')
            ->expectsOutputToContain('token:')
            ->assertSuccessful();

        $invitation = AdminUserInvitation::query()->firstOrFail();

        $this->assertSame(1, (int)$invitation->role);
        $this->assertSame(
            ['read_admin_user', 'write_admin_user'],
            $invitation->permissions->pluck('permission')->all(),
        );
    }

    #[Test]
    public function failsWithInvalidPermission(): void
    {
        $this->artisan('admin:invite invalid_permission')
            ->expectsOutput('不正な権限です: invalid_permission')
            ->assertFailed();
    }

    #[Test]
    public function storedTokenHashHasExpectedShape(): void
    {
        $this->artisan('admin:invite')->assertSuccessful();

        $invitation = AdminUserInvitation::query()->firstOrFail();
        $storedHash = $invitation->token_hash;

        // HMAC sha256 hex = 64 文字
        $this->assertSame(64, strlen($storedHash));
        $this->assertMatchesRegularExpression('/\A[0-9a-f]{64}\z/', $storedHash);
    }
}
