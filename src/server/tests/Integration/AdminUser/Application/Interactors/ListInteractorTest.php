<?php

declare(strict_types=1);

namespace Tests\Integration\AdminUser\Application\Interactors;

use AdminUser\Application\Interactors\ListInteractor;
use AdminUser\Application\UseCase\List\ListInputData;
use AdminUser\DebugInfrastructures\FileAdminUserRepository;
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

        $id1 = $this->generateUuid();
        $id2 = $this->generateUuid();
        $date1 = (new DateTimeImmutable())->modify('-1 day')->format('Y-m-d H:i:s');
        $date2 = (new DateTimeImmutable())->format('Y-m-d H:i:s');

        $this->factory(FileAdminUserRepository::class, [
            [
                'admin_user_id' => $id1,
                'name' => 'ユーザー1',
                'email' => 'user1@example.com',
                'created_at' => $date1,
                'role' => Role::General->value,
                'permissions' => [],
                'hashed_password' => 'password',
            ],
            [
                'admin_user_id' => $id2,
                'name' => 'ユーザー2',
                'email' => 'user2@example.com',
                'created_at' => $date2,
                'role' => Role::General->value,
                'permissions' => [],
                'hashed_password' => 'password',
            ],
        ]);

        $result = $this->getInstance()->handle(new ListInputData());

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();

        $this->assertCount(2, $output->adminUsers);
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
