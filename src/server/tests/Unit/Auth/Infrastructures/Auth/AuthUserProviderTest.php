<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Infrastructures\Auth;

use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\Email;
use Auth\Domain\Models\AuthAdminUserRepositoryInterface;
use Auth\Domain\Models\AuthenticatableAdminUser;
use Auth\Infrastructures\Auth\AuthUser;
use Auth\Infrastructures\Auth\AuthUserProvider;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthUserProviderTest extends TestCase
{
    private AuthAdminUserRepositoryInterface&MockInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(AuthAdminUserRepositoryInterface::class);
    }

    #[Test]
    public function retrieveById(): void
    {
        $adminUserId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

        $this->repository->shouldReceive('find')
            ->withArgs(fn (AdminUserId $arg): bool => $arg->value === $adminUserId)
            ->andReturn($this->createAuthenticatableUser($adminUserId))
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
            ->andReturn($this->createAuthenticatableUser('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'))
            ->once();

        $result = $this->getInstance()->retrieveByCredentials([
            'email' => $email,
            'password' => 'plain-password',
        ]);

        $this->assertInstanceOf(AuthUser::class, $result);
        $this->assertSame('', $result->getAuthPassword());
    }

    #[Test]
    public function validateCredentials(): void
    {
        $user = new AuthUser(
            AdminUserId::reconstruct('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
        );

        $result = $this->getInstance()->validateCredentials($user, ['password' => 'plain-password']);

        $this->assertFalse($result);
    }

    private function createAuthenticatableUser(string $adminUserId): AuthenticatableAdminUser
    {
        return new AuthenticatableAdminUser(
            AdminUserId::reconstruct($adminUserId),
        );
    }

    private function getInstance(): AuthUserProvider
    {
        return new AuthUserProvider(
            $this->repository,
        );
    }
}
