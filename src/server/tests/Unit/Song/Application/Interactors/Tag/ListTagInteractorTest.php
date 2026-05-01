<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Interactors\Tag;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Interactors\Tag\ListTagInteractor;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class ListTagInteractorTest extends TestCase
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
    public function listTags(): void
    {
        $this->repository->shouldReceive('all')
            ->once()
            ->andReturn([
                $this->createSongTag('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'タグA', 10),
                $this->createSongTag('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'タグB', 20),
            ]);

        $result = $this->getInstance($this->privilegedContext())->handle();

        $this->assertTrue($result->isOk());

        $tags = $result->unwrap()->tags;

        $this->assertCount(2, $tags);
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $tags[0]->songTagId->value);
        $this->assertSame('タグA', $tags[0]->name->value);
        $this->assertSame(10, $tags[0]->orderNo->value);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $tags[1]->songTagId->value);
        $this->assertSame('タグB', $tags[1]->name->value);
        $this->assertSame(20, $tags[1]->orderNo->value);
    }

    #[Test]
    public function unauthenticated(): void
    {
        $result = $this->getInstance($this->app->make(AuthContext::class))->handle();

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

    private function getInstance(AuthContext $context): ListTagInteractor
    {
        return new ListTagInteractor($context, $this->repository);
    }
}
