<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Interactors\Tag;

use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Interactors\Tag\SearchInteractor;
use Song\Application\Query\SongTagQueryServiceInterface;
use Song\Application\UseCase\Tag\Search\SearchInputData;
use Song\Application\UseCase\Tag\Search\SearchOutputData;
use Song\Domain\Criteria\SongTagSearchCriteria;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagName;
use Support\Domain\ValueObjects\OrderNo;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class SearchInteractorTest extends TestCase
{
    use EntityFactory;

    private MockInterface&SongTagQueryServiceInterface $query;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->query = Mockery::mock(SongTagQueryServiceInterface::class);
    }

    #[Test]
    public function search(): void
    {
        $tags = [
            new SongTag(
                SongTagId::reconstruct($this->generateUuid()),
                SongTagName::reconstruct('ロック'),
                OrderNo::reconstruct(10),
            ),
        ];

        $this->query->shouldReceive('search')
            ->withArgs(fn (SongTagSearchCriteria $criteria): bool => $criteria->name->isEmpty())
            ->andReturn($tags)
            ->once();

        $this->query->shouldReceive('maxPage')
            ->withArgs(fn (SongTagSearchCriteria $criteria): bool => $criteria->name->isEmpty())
            ->andReturn(1)
            ->once();

        $result = $this->getInstance($this->privilegedContext())->handle(new SearchInputData());

        $this->assertTrue($result->isOk());
        /** @var SearchOutputData $output */
        $output = $result->unwrap();
        $this->assertSame($tags, $output->tags);
        $this->assertSame(1, $output->maxPage);
    }

    #[Test]
    public function searchWithName(): void
    {
        $this->query->shouldReceive('search')
            ->withArgs(fn (SongTagSearchCriteria $criteria): bool => $criteria->name->isPresent() && $criteria->name->get() === 'ロック')
            ->andReturn([])
            ->once();

        $this->query->shouldReceive('maxPage')
            ->withArgs(fn (SongTagSearchCriteria $criteria): bool => $criteria->name->isPresent() && $criteria->name->get() === 'ロック')
            ->andReturn(0)
            ->once();

        $result = $this->getInstance($this->privilegedContext())->handle(
            new SearchInputData(name: 'ロック'),
        );

        $this->assertTrue($result->isOk());
        $this->assertSame([], $result->unwrap()->tags);
    }

    #[Test]
    public function unauthenticated(): void
    {
        $context = $this->app->make(AuthContext::class);

        $result = $this->getInstance($context)->handle(new SearchInputData());

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthenticationError::class, $result->unwrapErr());
    }

    #[Test]
    public function unauthorized(): void
    {
        $context = $this->app->make(AuthContext::class);
        $context->set($this->createAdminUser($this->generateUuid(), 'general@example.com', Role::General));

        $result = $this->getInstance($context)->handle(new SearchInputData());

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthorizationError::class, $result->unwrapErr());
    }

    private function getInstance(AuthContext $context): SearchInteractor
    {
        return new SearchInteractor($context, $this->query);
    }
}
