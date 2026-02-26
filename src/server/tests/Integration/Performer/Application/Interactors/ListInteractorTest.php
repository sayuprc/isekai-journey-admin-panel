<?php

declare(strict_types=1);

namespace Tests\Integration\Performer\Application\Interactors;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
use DateTimeImmutable;
use Performer\Application\Interactors\ListInteractor;
use Performer\DebugInfrastructures\FilePerformerRepository;
use PHPUnit\Framework\Attributes\Test;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class ListInteractorTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function nonEmptyPerformers(): void
    {
        $this->privilegedContext();

        $uuid = $this->generateUuid();

        $this->factory(FilePerformerRepository::class, $this->createPerformer($uuid, 'ヰ世界情緒', 1)->toArray());

        $result = $this->getInstance()->handle();
        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertCount(1, $response->performers);

        $this->assertSame($uuid, $response->performers[0]->performerId->value);
        $this->assertSame('ヰ世界情緒', $response->performers[0]->name->value);
        $this->assertSame(1, $response->performers[0]->orderNo->value);
    }

    #[Test]
    public function emptyPerformers(): void
    {
        $this->privilegedContext();

        $result = $this->getInstance()->handle();
        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertCount(0, $response->performers);
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
