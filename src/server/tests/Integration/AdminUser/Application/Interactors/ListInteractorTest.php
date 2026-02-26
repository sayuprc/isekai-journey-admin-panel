<?php

declare(strict_types=1);

namespace Tests\Integration\AdminUser\Application\Interactors;

use AdminUser\Application\Interactors\ListInteractor;
use AdminUser\Application\UseCase\List\ListInputData;
use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\HashedPassword;
use AdminUser\Domain\Models\Permission;
use AdminUser\Domain\Models\Role;
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
        $this->permissionContext(Permission::ReadAdminUser);

        // Date1 is 1 day before Date2.
        $date1 = new DateTimeImmutable()->modify('-1 day');
        $date2 = new DateTimeImmutable();

        $repository = $this->app->make(AdminUserRepositoryInterface::class);

        $id1 = $this->generateUuid();
        $user1 = AdminUser::reconstruct(
            $id1,
            'ユーザー1',
            'user1@example.com',
            $date1,
            Role::General->value,
            [],
        );
        $repository->register($user1, HashedPassword::create('password')->unwrap());

        $id2 = $this->generateUuid();
        $user2 = AdminUser::reconstruct(
            $id2,
            'ユーザー2',
            'user2@example.com',
            $date2,
            Role::General->value,
            [],
        );
        $repository->register($user2, HashedPassword::create('password')->unwrap());

        $result = $this->getInstance()->handle(new ListInputData());

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();

        $this->assertCount(2, $output->adminUsers);
        // Note: FileAdminUserRepository sorts by createdAt ascending.
        $this->assertSame($id1, $output->adminUsers[0]->adminUserId->value);
        $this->assertSame($id2, $output->adminUsers[1]->adminUserId->value);
    }

    #[Test]
    public function cannotListWithoutPermission(): void
    {
        $this->permissionContext();

        $result = $this->getInstance()->handle(new ListInputData());

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthorizationError::class, $result->unwrapErr());
    }

    #[Test]
    public function cannotListUnauthenticated(): void
    {
        $result = $this->getInstance()->handle(new ListInputData());

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthenticationError::class, $result->unwrapErr());
    }

    private function getInstance(): ListInteractor
    {
        return $this->app->make(ListInteractor::class);
    }
}
