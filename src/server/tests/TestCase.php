<?php

declare(strict_types=1);

namespace Tests;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\Permission;
use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Support\Contracts\UuidGeneratorInterface;

abstract class TestCase extends BaseTestCase
{
    protected function generateUuid(): string
    {
        return $this->app->make(UuidGeneratorInterface::class)->generate();
    }

    protected function privilegedContext(): AuthContext
    {
        $context = $this->app->make(AuthContext::class);

        $context->set(AdminUser::reconstruct(
            $this->generateUuid(),
            'テストユーザー',
            'test@example.com',
            new DateTimeImmutable(),
            Role::Privilege->value,
            [],
        ));

        return $context;
    }

    protected function permissionContext(Permission ...$permissions): AuthContext
    {
        $context = $this->app->make(AuthContext::class);

        $context->set(AdminUser::reconstruct(
            $this->generateUuid(),
            'テストユーザー',
            'test@example.com',
            new DateTimeImmutable(),
            Role::General->value,
            array_map(fn (Permission $permission): string => $permission->value, $permissions),
        ));

        return $context;
    }
}
