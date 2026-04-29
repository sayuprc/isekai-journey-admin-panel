<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Interactors;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Interactors\SearchTagInteractor;
use Song\Application\UseCase\SearchTag\SearchInputData;
use Song\Domain\Criteria\Tag\SongTagSearchCriteria;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class SearchTagInteractorTest extends TestCase
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
    public function searchWithoutName(): void
    {
        $songTag = $this->createSongTag('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', '派生曲', 1);

        $this->repository->shouldReceive('search')
            ->withArgs(fn (SongTagSearchCriteria $criteria): bool => $criteria->name->isEmpty())
            ->andReturn([$songTag])
            ->once();

        $this->repository->shouldReceive('maxPage')
            ->withArgs(fn (SongTagSearchCriteria $criteria): bool => $criteria->name->isEmpty())
            ->andReturn(1)
            ->once();

        $result = $this->getInstance()->handle(new SearchInputData());

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();
        $this->assertCount(1, $output->tags);
        $this->assertSame(1, $output->maxPage);
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $output->tags[0]->songTagId->value);
    }

    #[Test]
    public function searchWithName(): void
    {
        $songTag = $this->createSongTag('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', '派生曲', 1);

        $this->repository->shouldReceive('search')
            ->withArgs(fn (SongTagSearchCriteria $criteria): bool => $criteria->name->isPresent() && $criteria->name->get() === '派生')
            ->andReturn([$songTag])
            ->once();

        $this->repository->shouldReceive('maxPage')
            ->withArgs(fn (SongTagSearchCriteria $criteria): bool => $criteria->name->isPresent() && $criteria->name->get() === '派生')
            ->andReturn(1)
            ->once();

        $result = $this->getInstance()->handle(new SearchInputData(name: '派生'));

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();
        $this->assertCount(1, $output->tags);
        $this->assertSame('派生曲', $output->tags[0]->name->value);
    }

    #[Test]
    public function returnAuthenticationError(): void
    {
        $this->repository->shouldNotReceive('search');
        $this->repository->shouldNotReceive('maxPage');

        $context = $this->app->make(AuthContext::class);

        $result = new SearchTagInteractor($context, $this->repository)->handle(new SearchInputData());

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthenticationError::class, $result->unwrapErr());
    }

    #[Test]
    public function returnAuthorizationError(): void
    {
        $this->repository->shouldNotReceive('search');
        $this->repository->shouldNotReceive('maxPage');

        $context = $this->app->make(AuthContext::class);

        $context->set(AdminUser::reconstruct(
            $this->generateUuid(),
            'テストユーザー',
            'test@example.com',
            new DateTimeImmutable(),
            Role::General->value,
            [],
        ));

        $result = new SearchTagInteractor($context, $this->repository)->handle(new SearchInputData());

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthorizationError::class, $result->unwrapErr());
    }

    private function getInstance(): SearchTagInteractor
    {
        return new SearchTagInteractor(
            $this->privilegedContext(),
            $this->repository,
        );
    }
}
