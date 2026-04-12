<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Interactors\Tag;

use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Interactors\Tag\GetInteractor;
use Song\Application\UseCase\Tag\Get\GetInputData;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagName;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\Domain\ValueObjects\OrderNo;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Support\UseCase\Error\NotFoundError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class GetInteractorTest extends TestCase
{
    use EntityFactory;

    private MockInterface&SongTagRepositoryInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(SongTagRepositoryInterface::class);
    }

    #[Test]
    public function getSongTag(): void
    {
        $songTagId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $tag = new SongTag(
            SongTagId::reconstruct($songTagId),
            SongTagName::reconstruct('ロック'),
            OrderNo::reconstruct(10),
        );

        $this->repository->shouldReceive('find')
            ->withArgs(fn (SongTagId $arg): bool => $arg->value === $songTagId)
            ->andReturn($tag)
            ->once();

        $result = $this->getInstance($this->privilegedContext())->handle(new GetInputData($songTagId));

        $this->assertTrue($result->isOk());
        $this->assertSame($tag, $result->unwrap()->tag);
    }

    #[Test]
    public function getSongTagFailsIfUnauthenticated(): void
    {
        $context = $this->app->make(AuthContext::class);

        $result = $this->getInstance($context)->handle(new GetInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthenticationError::class, $result->unwrapErr());
    }

    #[Test]
    public function getSongTagFailsIfNoPermission(): void
    {
        $context = $this->app->make(AuthContext::class);
        $context->set($this->createAdminUser($this->generateUuid(), 'general@example.com', Role::General));

        $result = $this->getInstance($context)->handle(new GetInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthorizationError::class, $result->unwrapErr());
    }

    #[Test]
    public function getSongTagFailsIfNotFound(): void
    {
        $songTagId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

        $this->repository->shouldReceive('find')
            ->withArgs(fn (SongTagId $arg): bool => $arg->value === $songTagId)
            ->andReturnNull()
            ->once();

        $result = $this->getInstance($this->privilegedContext())->handle(new GetInputData($songTagId));

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(NotFoundError::class, $error);
        $this->assertSame('楽曲タグ', $error->resourceName);
        $this->assertSame($songTagId, $error->identifier);
    }

    private function getInstance(AuthContext $context): GetInteractor
    {
        return new GetInteractor(
            $context,
            $this->repository,
        );
    }
}
