<?php

declare(strict_types=1);

namespace Tests\Unit\AdminUser\Domain\Models;

use AdminUser\Domain\Models\Permission;
use AdminUser\Domain\Models\Permissions;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\Error\DomainRuleViolationError;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    #[Test]
    public function fromArray(): void
    {
        $input = [Permission::ReadSong->value, Permission::WriteSong->value];
        $result = Permissions::fromArray($input);

        $this->assertTrue($result->isOk());
        $permissions = $result->unwrap();
        $this->assertCount(2, $permissions);
        $this->assertSame(Permission::ReadSong, $permissions[0]);
        $this->assertSame(Permission::WriteSong, $permissions[1]);
    }

    #[Test]
    public function fromArrayError(): void
    {
        $input = ['invalid_permission'];
        $result = Permissions::fromArray($input);

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(DomainRuleViolationError::class, $error);
        $this->assertSame('権限', $error->field);
        $this->assertSame('不正な権限です', $error->message);
    }

    #[Test]
    public function reconstruct(): void
    {
        $input = [Permission::ReadSong->value, Permission::WriteSong->value];
        $permissions = Permissions::reconstruct($input);

        $this->assertCount(2, $permissions);
        $this->assertSame(Permission::ReadSong, $permissions[0]);
        $this->assertSame(Permission::WriteSong, $permissions[1]);
    }

    #[Test]
    public function toArray(): void
    {
        $input = [Permission::ReadSong->value, Permission::WriteSong->value];
        $permissions = Permissions::reconstruct($input);

        $this->assertSame($input, $permissions->toArray());
    }
}
