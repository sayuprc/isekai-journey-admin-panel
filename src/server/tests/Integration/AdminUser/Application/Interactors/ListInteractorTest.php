<?php

declare(strict_types=1);

namespace Tests\Integration\AdminUser\Application\Interactors;

use AdminUser\Application\Interactors\ListInteractor;
use AdminUser\Application\UseCase\List\ListInputData;
use AdminUser\DebugInfrastructures\FileAdminUserRepository;
use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class ListInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function canList(): void
    {
        $this->privilegedContext();

        $uuid = $this->generateUuid();
        $user = AdminUser::reconstruct(
            $uuid,
            'テストユーザー',
            'test@example.com',
            new DateTimeImmutable(),
            Role::General->value,
            [],
        );

        $this->factory(FileAdminUserRepository::class, $user->toArray());

        $result = $this->getInstance()->handle(new ListInputData());

        $this->assertTrue($result->isOk());

        $users = $result->unwrap()->adminUsers;
        $this->assertCount(1, $users);
        $this->assertSame($uuid, $users[0]->adminUserId->value);
    }

    #[Test]
    public function emptyAdminUsers(): void
    {
        $this->privilegedContext();

        // No users created

        $result = $this->getInstance()->handle(new ListInputData());

        $this->assertTrue($result->isOk());

        $users = $result->unwrap()->adminUsers;
        $this->assertCount(0, $users);
    }

    #[Test]
    public function cannotListWhenUnauthenticated(): void
    {
        $result = $this->getInstance()->handle(new ListInputData());

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthenticationError::class, $result->unwrapErr());
    }

    #[Test]
    public function cannotListWhenUnauthorized(): void
    {
        $context = $this->app->make(AuthContext::class);
        $context->set(AdminUser::reconstruct(
            $this->generateUuid(),
            'Unprivileged User',
            'unprivileged@example.com',
            new DateTimeImmutable(),
            Role::General->value,
            [], // No permissions
        ));

        $result = $this->getInstance()->handle(new ListInputData());

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthorizationError::class, $result->unwrapErr());
    }

    private function getInstance(): ListInteractor
    {
        return $this->app->make(ListInteractor::class);
    }
}
