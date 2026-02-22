<?php

declare(strict_types=1);

namespace Tests\Unit\AdminUser\Infrastructures;

use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\AdminUserName;
use AdminUser\Domain\Models\CreatedAt;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\Role;
use AdminUser\Infrastructures\AdminUserFactory;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminUserFactoryTest extends TestCase
{
    #[Test]
    public function canCreate(): void
    {
        $createdAt = CreatedAt::reconstruct(new DateTimeImmutable('2026-01-01 00:00:00'));

        $user = $this->getInstance()->create(
            AdminUserId::reconstruct('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
            AdminUserName::reconstruct('テストユーザー'),
            Email::reconstruct('example@example.com'),
            $createdAt,
            Role::General,
            Permissions::reconstruct([]),
        );

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $user->userId->value);
        $this->assertSame('テストユーザー', $user->adminUserName->value);
        $this->assertSame('example@example.com', $user->email->value);
        $this->assertSame('2026-01-01 00:00:00', $user->createdAt->value->format('Y-m-d H:i:s'));
        $this->assertSame(Role::General, $user->role);
        $this->assertSame([], $user->permissions->toArray());
    }

    private function getInstance(): AdminUserFactory
    {
        return new AdminUserFactory();
    }
}
