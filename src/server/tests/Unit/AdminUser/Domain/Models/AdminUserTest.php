<?php

declare(strict_types=1);

namespace Tests\Unit\AdminUser\Domain\Models;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\Permission;
use AdminUser\Domain\Models\Role;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    #[Test]
    #[DataProvider('equalsDataProvider')]
    public function equals(AdminUser $object, AdminUser $other, bool $expected): void
    {
        $this->assertSame($expected, $object->equals($other));
    }

    #[Test]
    public function toArray(): void
    {
        $adminUser = AdminUser::reconstruct(
            '11111111-1111-1111-1111-111111111111',
            'ユーザー1',
            'admin@example.com',
            new DateTimeImmutable('2026-01-01 00:00:00'),
            Role::General->value,
            [Permission::ReadSong->value, Permission::WriteSong->value],
        );

        $expected = [
            'admin_user_id' => '11111111-1111-1111-1111-111111111111',
            'name' => 'ユーザー1',
            'email' => 'admin@example.com',
            'created_at' => '2026-01-01 00:00:00',
            'role' => Role::General->value,
            'permissions' => [Permission::ReadSong->value, Permission::WriteSong->value],
        ];

        $this->assertSame($expected, $adminUser->toArray());
    }

    #[Test]
    #[DataProvider('canDataProvider')]
    public function can(AdminUser $adminUser, Permission $permission, bool $expected): void
    {
        $this->assertSame($expected, $adminUser->can($permission));
    }

    public static function canDataProvider(): array
    {
        return [
            'Privilege user can do anything' => [
                AdminUser::reconstruct(
                    '11111111-1111-1111-1111-111111111111',
                    'Admin',
                    'admin@example.com',
                    new DateTimeImmutable(),
                    Role::Privilege->value,
                    [] // No explicit permissions
                ),
                Permission::ReadSong,
                true,
            ],
            'General user with permission can do it' => [
                AdminUser::reconstruct(
                    '11111111-1111-1111-1111-111111111111',
                    'User',
                    'user@example.com',
                    new DateTimeImmutable(),
                    Role::General->value,
                    [Permission::ReadSong->value]
                ),
                Permission::ReadSong,
                true,
            ],
            'General user without permission cannot do it' => [
                AdminUser::reconstruct(
                    '11111111-1111-1111-1111-111111111111',
                    'User',
                    'user@example.com',
                    new DateTimeImmutable(),
                    Role::General->value,
                    [Permission::ReadSong->value]
                ),
                Permission::WriteSong,
                false,
            ],
        ];
    }

    public static function equalsDataProvider(): array
    {
        return [
            [
                AdminUser::reconstruct(
                    '11111111-1111-1111-1111-111111111111',
                    'ユーザー1',
                    'admin@example.com',
                    new DateTimeImmutable('2026-01-01 00:00:00'),
                    Role::General->value,
                    [],
                ),
                AdminUser::reconstruct(
                    '11111111-1111-1111-1111-111111111111',
                    'ユーザー1',
                    'admin@example.com',
                    new DateTimeImmutable('2026-01-01 00:00:00'),
                    Role::General->value,
                    [],
                ),
                true,
            ],
            [
                AdminUser::reconstruct(
                    '11111111-1111-1111-1111-111111111111',
                    'ユーザー1',
                    'first@example.com',
                    new DateTimeImmutable('2026-01-01 00:00:00'),
                    Role::General->value,
                    [Permission::ReadSong->value],
                ),
                AdminUser::reconstruct(
                    '11111111-1111-1111-1111-111111111111',
                    'ユーザー2',
                    'second@example.com',
                    new DateTimeImmutable('2026-01-02 00:00:00'),
                    Role::Privilege->value,
                    [Permission::WriteSong->value],
                ),
                true,
            ],
            [
                AdminUser::reconstruct(
                    '11111111-1111-1111-1111-111111111111',
                    'ユーザー1',
                    'admin@example.com',
                    new DateTimeImmutable('2026-01-01 00:00:00'),
                    Role::General->value,
                    [],
                ),
                AdminUser::reconstruct(
                    '22222222-2222-2222-2222-222222222222',
                    'ユーザー2',
                    'admin@example.com',
                    new DateTimeImmutable('2026-01-01 00:00:00'),
                    Role::General->value,
                    [],
                ),
                false,
            ],
            [
                AdminUser::reconstruct(
                    '11111111-1111-1111-1111-111111111111',
                    'ユーザー1',
                    'first@example.com',
                    new DateTimeImmutable('2026-01-01 00:00:00'),
                    Role::General->value,
                    [Permission::ReadSong->value],
                ),
                AdminUser::reconstruct(
                    '22222222-2222-2222-2222-222222222222',
                    'ユーザー2',
                    'second@example.com',
                    new DateTimeImmutable('2026-01-02 00:00:00'),
                    Role::Privilege->value,
                    [Permission::WriteSong->value],
                ),
                false,
            ],
        ];
    }
}
