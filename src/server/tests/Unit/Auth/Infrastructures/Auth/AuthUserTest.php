<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Infrastructures\Auth;

use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\HashedPassword;
use Auth\Infrastructures\Auth\AuthUser;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthUserTest extends TestCase
{
    #[Test]
    public function authenticatableContractValues(): void
    {
        $user = new AuthUser(
            AdminUserId::reconstruct('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
            HashedPassword::reconstruct('hashed-password'),
        );

        $this->assertSame('adminUserId', $user->getAuthIdentifierName());
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $user->getAuthIdentifier());
        $this->assertSame('password', $user->getAuthPasswordName());
        $this->assertSame('hashed-password', $user->getAuthPassword());
        $this->assertSame('', $user->getRememberToken());
        $this->assertSame('', $user->getRememberTokenName());
    }
}
