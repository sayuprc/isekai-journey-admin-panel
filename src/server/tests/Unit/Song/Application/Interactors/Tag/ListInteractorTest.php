<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Interactors\Tag;

use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use ResultType\Ok;
use Song\Application\Interactors\Tag\ListInteractor;
use Song\Application\UseCase\Tag\List\ListOutputData;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagName;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\Domain\ValueObjects\OrderNo;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class ListInteractorTest extends TestCase
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
    public function list(): void
    {
        $tags = [
            new SongTag(
                SongTagId::reconstruct($this->generateUuid()),
                SongTagName::reconstruct('ロック'),
                OrderNo::reconstruct(10),
            ),
            new SongTag(
                SongTagId::reconstruct($this->generateUuid()),
                SongTagName::reconstruct('ポップ'),
                OrderNo::reconstruct(20),
            ),
        ];

        $this->repository->shouldReceive('all')
            ->andReturn($tags)
            ->once();

        $result = $this->getInstance($this->privilegedContext())->handle();

        $this->assertTrue($result->isOk());
        /** @var Ok<ListOutputData> $result */
        $this->assertSame($tags, $result->unwrap()->tags);
    }

    #[Test]
    public function unauthenticated(): void
    {
        $context = $this->app->make(AuthContext::class);

        $result = $this->getInstance($context)->handle();

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthenticationError::class, $result->unwrapErr());
    }

    #[Test]
    public function unauthorized(): void
    {
        $context = $this->app->make(AuthContext::class);
        $context->set($this->createAdminUser($this->generateUuid(), 'general@example.com', Role::General));

        $result = $this->getInstance($context)->handle();

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthorizationError::class, $result->unwrapErr());
    }

    private function getInstance(AuthContext $context): ListInteractor
    {
        return new ListInteractor($context, $this->repository);
    }
}
