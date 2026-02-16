<?php

declare(strict_types=1);

namespace Tests\Unit\AdminUser\Infrastructures;

use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\PlainPassword;
use AdminUser\Domain\Models\Role;
use AdminUser\Domain\Services\HasherInterface;
use AdminUser\Infrastructures\AdminUserFactory;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminUserFactoryTest extends TestCase
{
    private HasherInterface&MockInterface $hasher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hasher = Mockery::mock(HasherInterface::class);
    }

    #[Test]
    public function canCreate(): void
    {
        $this->hasher->shouldReceive('hash')
            ->with('plain')
            ->andReturn('hashed')
            ->once();

        $user = $this->getInstance()->create(
            AdminUserId::reconstruct('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
            Email::reconstruct('example@example.com'),
            PlainPassword::reconstruct('plain'),
            Role::General,
            Permissions::reconstruct([]),
        );

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $user->userId->value);
        $this->assertSame('example@example.com', $user->email->value);
        $this->assertSame('hashed', $user->hashedPassword->value);
        $this->assertSame(Role::General, $user->role);
        $this->assertSame([], $user->permissions->toArray());
    }

    private function getInstance(): AdminUserFactory
    {
        return new AdminUserFactory($this->hasher);
    }
}
