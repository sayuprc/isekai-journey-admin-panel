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
use Song\Application\Interactors\SearchInteractor;
use Song\Application\Query\SongQueryServiceInterface;
use Song\Application\Query\SongSummary;
use Song\Application\UseCase\Search\SearchInputData;
use Song\Domain\Models\SongAttribute;
use Song\Domain\Models\SongType;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class SearchInteractorTest extends TestCase
{
    use EntityFactory;

    private MockInterface&SongQueryServiceInterface $query;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->query = Mockery::mock(SongQueryServiceInterface::class);
    }

    #[Test]
    public function searchWithoutFilters(): void
    {
        $summary = new SongSummary(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            '描き続けた君へ',
            SongType::Original,
            null,
            1,
        );

        $this->query->shouldReceive('search')
            ->withArgs(fn ($criteria): bool => $criteria->title->isEmpty() && $criteria->type->isEmpty() && $criteria->attribute->isEmpty())
            ->andReturn([$summary])
            ->once();

        $this->query->shouldReceive('maxPage')
            ->withArgs(fn ($criteria): bool => $criteria->title->isEmpty() && $criteria->type->isEmpty() && $criteria->attribute->isEmpty())
            ->andReturn(1)
            ->once();

        $result = $this->getInstance()->handle(new SearchInputData());

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();
        $this->assertCount(1, $output->songs);
        $this->assertSame(1, $output->maxPage);
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $output->songs[0]->songId);
    }

    #[Test]
    public function searchWithTitle(): void
    {
        $summary = new SongSummary(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            '描き続けた君へ',
            SongType::Original,
            null,
            1,
        );

        $this->query->shouldReceive('search')
            ->withArgs(fn ($criteria): bool => $criteria->title->isPresent() && $criteria->title->get() === '描き続けた君へ')
            ->andReturn([$summary])
            ->once();

        $this->query->shouldReceive('maxPage')
            ->withArgs(fn ($criteria): bool => $criteria->title->isPresent() && $criteria->title->get() === '描き続けた君へ')
            ->andReturn(1)
            ->once();

        $result = $this->getInstance()->handle(new SearchInputData(title: '描き続けた君へ'));

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();
        $this->assertCount(1, $output->songs);
        $this->assertSame('描き続けた君へ', $output->songs[0]->title);
    }

    #[Test]
    public function searchWithType(): void
    {
        $summary = new SongSummary(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            '描き続けた君へ',
            SongType::Original,
            null,
            1,
        );

        $this->query->shouldReceive('search')
            ->withArgs(fn ($criteria): bool => $criteria->type->isPresent() && $criteria->type->get() === SongType::Original)
            ->andReturn([$summary])
            ->once();

        $this->query->shouldReceive('maxPage')
            ->withArgs(fn ($criteria): bool => $criteria->type->isPresent() && $criteria->type->get() === SongType::Original)
            ->andReturn(1)
            ->once();

        $result = $this->getInstance()->handle(new SearchInputData(type: SongType::Original->value));

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();
        $this->assertCount(1, $output->songs);
        $this->assertSame(SongType::Original, $output->songs[0]->type);
    }

    #[Test]
    public function searchWithAttribute(): void
    {
        $summary = new SongSummary(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            '描き続けた君へ',
            SongType::Original,
            SongAttribute::Collaboration,
            1,
        );

        $this->query->shouldReceive('search')
            ->withArgs(fn ($criteria): bool => $criteria->attribute->isPresent() && $criteria->attribute->get() === SongAttribute::Collaboration)
            ->andReturn([$summary])
            ->once();

        $this->query->shouldReceive('maxPage')
            ->withArgs(fn ($criteria): bool => $criteria->attribute->isPresent() && $criteria->attribute->get() === SongAttribute::Collaboration)
            ->andReturn(1)
            ->once();

        $result = $this->getInstance()->handle(new SearchInputData(attribute: SongAttribute::Collaboration->value));

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();
        $this->assertCount(1, $output->songs);
        $this->assertSame(SongAttribute::Collaboration, $output->songs[0]->attribute);
    }

    #[Test]
    public function returnAuthenticationError(): void
    {
        $this->query->shouldNotReceive('search');
        $this->query->shouldNotReceive('maxPage');

        $context = $this->app->make(AuthContext::class);

        $result = new SearchInteractor($context, $this->query)->handle(new SearchInputData());

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthenticationError::class, $result->unwrapErr());
    }

    #[Test]
    public function returnAuthorizationError(): void
    {
        $this->query->shouldNotReceive('search');
        $this->query->shouldNotReceive('maxPage');

        $context = $this->app->make(AuthContext::class);

        $context->set(AdminUser::reconstruct(
            $this->generateUuid(),
            'テストユーザー',
            'test@example.com',
            new DateTimeImmutable(),
            Role::General->value,
            [],
        ));

        $result = new SearchInteractor($context, $this->query)->handle(new SearchInputData());

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthorizationError::class, $result->unwrapErr());
    }

    private function getInstance(): SearchInteractor
    {
        return new SearchInteractor(
            $this->privilegedContext(),
            $this->query,
        );
    }
}
