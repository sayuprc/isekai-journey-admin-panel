<?php

declare(strict_types=1);

namespace Tests\Unit\SongTag\Application\Interactors;

use Auth\Domain\Models\AuthContext;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\TagRepositoryInterface;
use SongTag\Application\Interactors\SearchInteractor;
use SongTag\Application\UseCase\Search\SearchInputData;
use SongTag\Domain\Criteria\TagSearchCriteria;
use Support\UseCase\Error\AuthenticationError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class SearchInteractorTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TagRepositoryInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(TagRepositoryInterface::class);
    }

    #[Test]
    public function searchWithoutName(): void
    {
        $tag = $this->createSongTag('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'ライブ定番', 1);

        $this->repository->shouldReceive('search')
            ->withArgs(fn (TagSearchCriteria $criteria): bool => $criteria->name->isEmpty())
            ->andReturn([$tag])
            ->once();

        $this->repository->shouldReceive('maxPage')
            ->withArgs(fn (TagSearchCriteria $criteria): bool => $criteria->name->isEmpty())
            ->andReturn(1)
            ->once();

        $result = $this->getInstance()->handle(new SearchInputData());

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();
        $this->assertCount(1, $output->tags);
        $this->assertSame(1, $output->maxPage);
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $output->tags[0]->tagId->value);
    }

    #[Test]
    public function searchWithName(): void
    {
        $tag = $this->createSongTag('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'ライブ定番', 1);

        $this->repository->shouldReceive('search')
            ->withArgs(fn (TagSearchCriteria $criteria): bool => $criteria->name->isPresent() && $criteria->name->get() === 'ライブ')
            ->andReturn([$tag])
            ->once();

        $this->repository->shouldReceive('maxPage')
            ->withArgs(fn (TagSearchCriteria $criteria): bool => $criteria->name->isPresent() && $criteria->name->get() === 'ライブ')
            ->andReturn(1)
            ->once();

        $result = $this->getInstance()->handle(new SearchInputData(name: 'ライブ'));

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();
        $this->assertCount(1, $output->tags);
        $this->assertSame('ライブ定番', $output->tags[0]->name->value);
    }

    #[Test]
    public function returnAuthenticationError(): void
    {
        $this->repository->shouldNotReceive('search');
        $this->repository->shouldNotReceive('maxPage');

        $context = $this->app->make(AuthContext::class);

        $result = new SearchInteractor($context, $this->repository)->handle(new SearchInputData());

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthenticationError::class, $result->unwrapErr());
    }

    private function getInstance(): SearchInteractor
    {
        return new SearchInteractor(
            $this->privilegedContext(),
            $this->repository,
        );
    }
}
