<?php

declare(strict_types=1);

namespace Tests;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;

abstract class TestCase extends BaseTestCase
{
    protected function generateUuid(): string
    {
        return $this->app->make(UuidGeneratorInterface::class)->generate();
    }

    protected function toUuid(string $bin): string
    {
        return $this->app->make(UuidConverterInterface::class)->toUuid($bin);
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
}
