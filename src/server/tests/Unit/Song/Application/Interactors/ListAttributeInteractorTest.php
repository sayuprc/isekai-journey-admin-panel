<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Interactors;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Interactors\ListAttributeInteractor;
use Song\Domain\Models\SongAttribute;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Tests\TestCase;

class ListAttributeInteractorTest extends TestCase
{
    #[Test]
    public function listAttributes(): void
    {
        $result = $this->getInstance($this->privilegedContext())->handle();

        $this->assertTrue($result->isOk());

        $attributes = $result->unwrap()->attributes;

        $this->assertCount(5, $attributes);
        $this->assertSame(SongAttribute::cases(), $attributes);
    }

    #[Test]
    public function unauthenticated(): void
    {
        $context = $this->app->make(AuthContext::class);

        $result = $this->getInstance($context)->handle();

        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(AuthenticationError::class, $result->unwrapErr());
    }

    #[Test]
    public function unauthorized(): void
    {
        $context = $this->app->make(AuthContext::class);

        $context->set(AdminUser::reconstruct(
            $this->generateUuid(),
            'テストユーザー',
            'test@example.com',
            new DateTimeImmutable(),
            Role::General->value,
            [],
        ));

        $result = $this->getInstance($context)->handle();

        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(AuthorizationError::class, $result->unwrapErr());
    }

    private function getInstance(AuthContext $context): ListAttributeInteractor
    {
        return new ListAttributeInteractor($context);
    }
}
