<?php

declare(strict_types=1);

namespace Tests\Unit\AdminUser\Domain\Models;

use AdminUser\Domain\Models\Permission;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    #[Test]
    #[DataProvider('getNameDataProvider')]
    public function getName(Permission $permission, string $expected): void
    {
        $this->assertSame($expected, $permission->getName());
    }

    public static function getNameDataProvider(): array
    {
        return [
            [Permission::ReadAdminUser, '管理ユーザー閲覧'],
            [Permission::WriteAdminUser, '管理ユーザー編集'],
            [Permission::ReadCreator, 'クリエイター閲覧'],
            [Permission::WriteCreator, 'クリエイター編集'],
            [Permission::ReadPerformer, '共演者閲覧'],
            [Permission::WritePerformer, '共演者編集'],
            [Permission::ReadSong, '楽曲閲覧'],
            [Permission::WriteSong, '楽曲編集'],
            [Permission::ReadSongType, '楽曲種別閲覧'],
        ];
    }
}
