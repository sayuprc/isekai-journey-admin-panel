<?php

declare(strict_types=1);

namespace Tests;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
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
            'test@example.com',
            'hashed-password',
            Role::Privilege->value,
            [],
        ));

        return $context;
    }
}
