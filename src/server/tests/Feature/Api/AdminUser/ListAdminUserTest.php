<?php

declare(strict_types=1);

namespace Tests\Feature\Api\AdminUser;

use AdminUser\DebugInfrastructures\FileAdminUserRepository;
use AdminUser\Domain\Models\Role;
use AdminUser\Route\AdminUserRouteMap;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\WithAuth;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class ListAdminUserTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;
    use WithAuth;

    #[Test]
    public function showList(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(
            FileAdminUserRepository::class,
            $this->createAdminUser(
                $uuid,
                'admin@example.com',
                Role::Console,
                [],
                new DateTimeImmutable('2019-12-09 10:20:30'),
                'コンソールユーザー',
            )->toArray(),
        );

        $this->withAuth()
            ->get(route(AdminUserRouteMap::List))
            ->assertStatus(200)
            ->assertJson([
                'adminUsers' => [
                    [
                        'adminUserId' => $uuid,
                        'name' => 'コンソールユーザー',
                        'email' => 'admin@example.com',
                        'createdAt' => '2019-12-09T10:20:30+09:00',
                        'role' => [
                            'name' => Role::Console->getName(),
                            'value' => Role::Console->value,
                        ],
                        'permissions' => [],
                    ],
                    [
                        'name' => 'テストユーザー',
                        'role' => [
                            'name' => Role::Privilege->getName(),
                            'value' => Role::Privilege->value,
                        ],
                    ],
                ],
            ]);
    }
}
