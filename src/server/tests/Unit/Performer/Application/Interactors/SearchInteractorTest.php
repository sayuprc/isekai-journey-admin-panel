<?php

declare(strict_types=1);

namespace Tests\Unit\Performer\Application\Interactors;

use Auth\Domain\Models\AuthContext;
use Mockery;
use Mockery\MockInterface;
use Performer\Application\Interactors\SearchInteractor;
use Performer\Application\UseCase\Search\SearchInputData;
use Performer\Domain\Models\PerformerRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\UseCase\Error\AuthenticationError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class SearchInteractorTest extends TestCase
{
    use EntityFactory;

    private MockInterface&PerformerRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(PerformerRepositoryInterface::class);
    }

    #[Test]
    public function searchWithoutName(): void
    {
        $performer = $this->createPerformer('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'ヰ世界情緒', 1);

        $this->repository->shouldReceive('search')
            ->withArgs(fn ($criteria): bool => $criteria->name->isEmpty())
            ->andReturn([$performer])
            ->once();

        $this->repository->shouldReceive('maxPage')
            ->withArgs(fn ($criteria): bool => $criteria->name->isEmpty())
            ->andReturn(1)
            ->once();

        $result = $this->getInstance()->handle(new SearchInputData());

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();
        $this->assertCount(1, $output->performers);
        $this->assertSame(1, $output->maxPage);
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $output->performers[0]->performerId->value);
    }

    #[Test]
    public function searchWithName(): void
    {
        $performer = $this->createPerformer('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'ヰ世界情緒', 1);

        $this->repository->shouldReceive('search')
            ->withArgs(fn ($criteria): bool => $criteria->name->isPresent() && $criteria->name->get() === 'ヰ世界情緒')
            ->andReturn([$performer])
            ->once();

        $this->repository->shouldReceive('maxPage')
            ->withArgs(fn ($criteria): bool => $criteria->name->isPresent() && $criteria->name->get() === 'ヰ世界情緒')
            ->andReturn(1)
            ->once();

        $result = $this->getInstance()->handle(new SearchInputData(name: 'ヰ世界情緒'));

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();
        $this->assertCount(1, $output->performers);
        $this->assertSame('ヰ世界情緒', $output->performers[0]->name->value);
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
