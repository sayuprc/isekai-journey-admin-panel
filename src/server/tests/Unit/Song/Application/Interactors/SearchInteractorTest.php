<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Interactors;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Assemble\AssembledSong;
use Song\Application\Assemble\SongAssembler;
use Song\Application\Interactors\SearchInteractor;
use Song\Application\UseCase\Search\SearchInputData;
use Song\Domain\Models\SongAttribute;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\SongType;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class SearchInteractorTest extends TestCase
{
    use EntityFactory;

    private MockInterface&SongRepositoryInterface $repository;

    private MockInterface&SongAssembler $assembler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(SongRepositoryInterface::class);
        $this->assembler = Mockery::mock(SongAssembler::class);
    }

    #[Test]
    public function searchWithoutFilters(): void
    {
        $song = $this->createSong(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            '描き続けた君へ',
            'オリジナル楽曲',
            SongType::Original,
            null,
            1,
            [],
            [],
            [],
        );

        $assembledSong = new AssembledSong(
            $song->songId->value,
            $song->title->value,
            $song->description->value,
            $song->type->getName(),
            $song->type->value,
            null,
            null,
            $song->orderNo->value,
            [],
            [],
            [],
        );

        $this->repository->shouldReceive('search')
            ->withArgs(fn ($criteria): bool => $criteria->title->isEmpty() && $criteria->type->isEmpty() && $criteria->attribute->isEmpty())
            ->andReturn([$song])
            ->once();

        $this->repository->shouldReceive('maxPage')
            ->withArgs(fn ($criteria): bool => $criteria->title->isEmpty() && $criteria->type->isEmpty() && $criteria->attribute->isEmpty())
            ->andReturn(1)
            ->once();

        $this->assembler->shouldReceive('assemble')
            ->with($song)
            ->andReturn($assembledSong)
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
        $song = $this->createSong(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            '描き続けた君へ',
            'オリジナル楽曲',
            SongType::Original,
            null,
            1,
            [],
            [],
            [],
        );

        $assembledSong = new AssembledSong(
            $song->songId->value,
            $song->title->value,
            $song->description->value,
            $song->type->getName(),
            $song->type->value,
            null,
            null,
            $song->orderNo->value,
            [],
            [],
            [],
        );

        $this->repository->shouldReceive('search')
            ->withArgs(fn ($criteria): bool => $criteria->title->isPresent() && $criteria->title->get() === '描き続けた君へ')
            ->andReturn([$song])
            ->once();

        $this->repository->shouldReceive('maxPage')
            ->withArgs(fn ($criteria): bool => $criteria->title->isPresent() && $criteria->title->get() === '描き続けた君へ')
            ->andReturn(1)
            ->once();

        $this->assembler->shouldReceive('assemble')
            ->with($song)
            ->andReturn($assembledSong)
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
        $song = $this->createSong(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            '描き続けた君へ',
            'オリジナル楽曲',
            SongType::Original,
            null,
            1,
            [],
            [],
            [],
        );

        $assembledSong = new AssembledSong(
            $song->songId->value,
            $song->title->value,
            $song->description->value,
            $song->type->getName(),
            $song->type->value,
            null,
            null,
            $song->orderNo->value,
            [],
            [],
            [],
        );

        $this->repository->shouldReceive('search')
            ->withArgs(fn ($criteria): bool => $criteria->type->isPresent() && $criteria->type->get() === SongType::Original)
            ->andReturn([$song])
            ->once();

        $this->repository->shouldReceive('maxPage')
            ->withArgs(fn ($criteria): bool => $criteria->type->isPresent() && $criteria->type->get() === SongType::Original)
            ->andReturn(1)
            ->once();

        $this->assembler->shouldReceive('assemble')
            ->with($song)
            ->andReturn($assembledSong)
            ->once();

        $result = $this->getInstance()->handle(new SearchInputData(type: SongType::Original->value));

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();
        $this->assertCount(1, $output->songs);
        $this->assertSame(SongType::Original->value, $output->songs[0]->typeValue);
    }

    #[Test]
    public function searchWithAttribute(): void
    {
        $song = $this->createSong(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            '描き続けた君へ',
            'オリジナル楽曲',
            SongType::Original,
            SongAttribute::Collaboration,
            1,
            [],
            [],
            [],
        );

        $assembledSong = new AssembledSong(
            $song->songId->value,
            $song->title->value,
            $song->description->value,
            $song->type->getName(),
            $song->type->value,
            $song->attribute?->getName(),
            $song->attribute?->value,
            $song->orderNo->value,
            [],
            [],
            [],
        );

        $this->repository->shouldReceive('search')
            ->withArgs(fn ($criteria): bool => $criteria->attribute->isPresent() && $criteria->attribute->get() === SongAttribute::Collaboration)
            ->andReturn([$song])
            ->once();

        $this->repository->shouldReceive('maxPage')
            ->withArgs(fn ($criteria): bool => $criteria->attribute->isPresent() && $criteria->attribute->get() === SongAttribute::Collaboration)
            ->andReturn(1)
            ->once();

        $this->assembler->shouldReceive('assemble')
            ->with($song)
            ->andReturn($assembledSong)
            ->once();

        $result = $this->getInstance()->handle(new SearchInputData(attribute: SongAttribute::Collaboration->value));

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();
        $this->assertCount(1, $output->songs);
        $this->assertSame(SongAttribute::Collaboration->value, $output->songs[0]->attributeValue);
    }

    #[Test]
    public function returnAuthenticationError(): void
    {
        $this->repository->shouldNotReceive('search');
        $this->repository->shouldNotReceive('maxPage');
        $this->assembler->shouldNotReceive('assemble');

        $context = $this->app->make(AuthContext::class);

        $result = new SearchInteractor($context, $this->repository, $this->assembler)->handle(new SearchInputData());

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthenticationError::class, $result->unwrapErr());
    }

    #[Test]
    public function returnAuthorizationError(): void
    {
        $this->repository->shouldNotReceive('search');
        $this->repository->shouldNotReceive('maxPage');
        $this->assembler->shouldNotReceive('assemble');

        $context = $this->app->make(AuthContext::class);

        $context->set(AdminUser::reconstruct(
            $this->generateUuid(),
            'テストユーザー',
            'test@example.com',
            new DateTimeImmutable(),
            Role::General->value,
            [],
        ));

        $result = new SearchInteractor($context, $this->repository, $this->assembler)->handle(new SearchInputData());

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthorizationError::class, $result->unwrapErr());
    }

    private function getInstance(): SearchInteractor
    {
        return new SearchInteractor(
            $this->privilegedContext(),
            $this->repository,
            $this->assembler,
        );
    }
}
