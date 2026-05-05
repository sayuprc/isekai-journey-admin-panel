<?php

declare(strict_types=1);

namespace Tests\Unit\Support\UseCase\Authorizer;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\Permission;
use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
use Auth\Infrastructures\Auth\UseCaseAuthorizationContext;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Tests\TestCase;

class UseCaseAuthorizerTest extends TestCase
{
    #[Test]
    public function returnsAuthenticationErrorWhenCurrentUserDoesNotExist(): void
    {
        $result = $this->createAuthorizer(new AuthContext())->require(Permission::ReadMedia);

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthenticationError::class, $result->unwrapErr());
    }

    #[Test]
    public function returnsAuthorizationErrorWhenCurrentUserDoesNotHavePermission(): void
    {
        $context = new AuthContext();
        $context->set($this->createGeneralUser([]));

        $result = $this->createAuthorizer($context)->require(Permission::ReadMedia);

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthorizationError::class, $result->unwrapErr());
    }

    #[Test]
    public function returnsOkWhenCurrentUserHasPermission(): void
    {
        $context = new AuthContext();
        $context->set($this->createGeneralUser([Permission::ReadMedia->value]));

        $result = $this->createAuthorizer($context)->require(Permission::ReadMedia);

        $this->assertTrue($result->isOk());
        $this->assertTrue($result->unwrap()->can(Permission::ReadMedia));
    }

    /**
     * @param list<string> $permissions
     */
    private function createGeneralUser(array $permissions): AdminUser
    {
        return AdminUser::reconstruct(
            $this->generateUuid(),
            'テストユーザー',
            'test@example.com',
            new DateTimeImmutable(),
            Role::General->value,
            $permissions,
        );
    }

    private function createAuthorizer(AuthContext $context): UseCaseAuthorizer
    {
        return new UseCaseAuthorizer(new UseCaseAuthorizationContext($context));
    }
}
