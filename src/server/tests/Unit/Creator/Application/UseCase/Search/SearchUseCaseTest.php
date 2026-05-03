<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Application\UseCase\Search;

use Auth\Domain\Models\AuthContext;
use Creator\Application\UseCase\Search\SearchInputData;
use Creator\Application\UseCase\Search\SearchUseCase;
use Creator\Domain\Criteria\CreatorSearchCriteria;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Support\UseCase\Error\AuthenticationError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class SearchUseCaseTest extends TestCase
{
    use EntityFactory;

    private CreatorRepositoryInterface&MockInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CreatorRepositoryInterface::class);
    }

    #[Test]
    public function searchWithoutName(): void
    {
        $creator = $this->createCreator('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'ヰ世界情緒', 1);

        $this->repository->shouldReceive('search')
            ->withArgs(fn (CreatorSearchCriteria $criteria): bool => $criteria->name->isEmpty())
            ->andReturn([$creator])
            ->once();

        $this->repository->shouldReceive('maxPage')
            ->withArgs(fn (CreatorSearchCriteria $criteria): bool => $criteria->name->isEmpty())
            ->andReturn(1)
            ->once();

        $result = $this->getInstance()->handle(new SearchInputData());

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();
        $this->assertCount(1, $output->creators);
        $this->assertSame(1, $output->maxPage);
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $output->creators[0]->creatorId->value);
    }

    #[Test]
    public function searchWithName(): void
    {
        $creator = $this->createCreator('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'ヰ世界情緒', 1);

        $this->repository->shouldReceive('search')
            ->withArgs(fn (CreatorSearchCriteria $criteria): bool => $criteria->name->isPresent() && $criteria->name->get() === 'ヰ世界情緒')
            ->andReturn([$creator])
            ->once();

        $this->repository->shouldReceive('maxPage')
            ->withArgs(fn (CreatorSearchCriteria $criteria): bool => $criteria->name->isPresent() && $criteria->name->get() === 'ヰ世界情緒')
            ->andReturn(1)
            ->once();

        $result = $this->getInstance()->handle(new SearchInputData(name: 'ヰ世界情緒'));

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();
        $this->assertCount(1, $output->creators);
        $this->assertSame('ヰ世界情緒', $output->creators[0]->name->value);
    }

    #[Test]
    public function returnAuthenticationError(): void
    {
        $this->repository->shouldNotReceive('search');
        $this->repository->shouldNotReceive('maxPage');

        $context = $this->app->make(AuthContext::class);

        $result = new SearchUseCase($this->authorizer($context), $this->repository)->handle(new SearchInputData());

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthenticationError::class, $result->unwrapErr());
    }

    private function getInstance(): SearchUseCase
    {
        return new SearchUseCase(
            $this->authorizer(),
            $this->repository,
        );
    }
}
