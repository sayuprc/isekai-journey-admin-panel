<?php

declare(strict_types=1);

namespace Tests\Integration\SongType\Application\Interactors;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use SongType\Application\Interactors\ListInteractor;
use SongType\Domain\Models\SongType;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Tests\TestCase;

class ListInteractorTest extends TestCase
{
    #[Test]
    public function canList(): void
    {
        $this->privilegedContext();

        $result = $this->getInstance()->handle();

        $this->assertTrue($result->isOk());

        $types = $result->unwrap()->songTypes;
        $this->assertCount(count(SongType::cases()), $types);
    }

    #[Test]
    public function cannotListWhenUnauthenticated(): void
    {
        $result = $this->getInstance()->handle();

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

        $result = $this->getInstance()->handle();

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthorizationError::class, $result->unwrapErr());
    }

    private function getInstance(): ListInteractor
    {
        return $this->app->make(ListInteractor::class);
    }
}
