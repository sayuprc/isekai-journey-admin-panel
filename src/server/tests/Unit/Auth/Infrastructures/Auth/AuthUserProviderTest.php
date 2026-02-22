<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Infrastructures\Auth;

use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\HashedPassword;
use AdminUser\Domain\Services\HasherInterface;
use Auth\Domain\Models\AuthAdminUserRepositoryInterface;
use Auth\Domain\Models\AuthenticatableAdminUser;
use Auth\Infrastructures\Auth\AuthUser;
use Auth\Infrastructures\Auth\AuthUserProvider;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthUserProviderTest extends TestCase
{
    private AuthAdminUserRepositoryInterface&MockInterface $repository;

    private HasherInterface&MockInterface $hasher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(AuthAdminUserRepositoryInterface::class);
        $this->hasher = Mockery::mock(HasherInterface::class);
    }

    #[Test]
    public function retrieveById(): void
    {
        $adminUserId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

        $this->repository->shouldReceive('find')
            ->withArgs(fn (AdminUserId $arg): bool => $arg->value === $adminUserId)
            ->andReturn($this->createAuthenticatableUser($adminUserId, 'hashed-password'))
            ->once();

        $result = $this->getInstance()->retrieveById($adminUserId);

        $this->assertInstanceOf(AuthUser::class, $result);
        $this->assertSame($adminUserId, $result->getAuthIdentifier());
    }

    #[Test]
    public function retrieveByIdInvalidIdentifier(): void
    {
        $this->repository->shouldNotReceive('find');

        $result = $this->getInstance()->retrieveById('invalid-id');

        $this->assertNull($result);
    }

    #[Test]
    public function retrieveByCredentials(): void
    {
        $email = 'example@example.com';

        $this->repository->shouldReceive('findByEmail')
            ->withArgs(fn (Email $arg): bool => $arg->value === $email)
            ->andReturn($this->createAuthenticatableUser('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'hashed-password'))
            ->once();

        $result = $this->getInstance()->retrieveByCredentials([
            'email' => $email,
            'password' => 'plain-password',
        ]);

        $this->assertInstanceOf(AuthUser::class, $result);
        $this->assertSame('hashed-password', $result->getAuthPassword());
    }

    #[Test]
    public function validateCredentials(): void
    {
        $this->hasher->shouldReceive('check')
            ->with('plain-password', 'hashed-password')
            ->andReturnTrue()
            ->once();

        $user = new AuthUser(
            AdminUserId::reconstruct('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
            HashedPassword::reconstruct('hashed-password'),
        );

        $result = $this->getInstance()->validateCredentials($user, ['password' => 'plain-password']);

        $this->assertTrue($result);
    }

    private function createAuthenticatableUser(string $adminUserId, string $hashedPassword): AuthenticatableAdminUser
    {
        return new AuthenticatableAdminUser(
            AdminUserId::reconstruct($adminUserId),
            HashedPassword::reconstruct($hashedPassword),
        );
    }

    private function getInstance(): AuthUserProvider
    {
        return new AuthUserProvider(
            $this->repository,
            $this->hasher,
        );
    }
}
