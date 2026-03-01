<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Interactors;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Assemble\AssembledCreator;
use Song\Application\Assemble\AssembledSong;
use Song\Application\Assemble\SongAssembler;
use Song\Application\Interactors\GetInteractor;
use Song\Application\UseCase\Get\GetInputData;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use SongType\Domain\Models\SongType;
use Support\UseCase\Error\NotFoundError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class GetInteractorTest extends TestCase
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
    public function getSong(): void
    {
        $songId = 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD';
        $lyricistId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $composerId = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB';
        $arrangerId = 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC';

        $song = $this->createSong(
            $songId,
            '描き続けた君へ',
            'オリジナル楽曲',
            SongType::Original,
            1,
            [['creatorId' => $lyricistId, 'orderNo' => 1]],
            [['creatorId' => $composerId, 'orderNo' => 1]],
            [['creatorId' => $arrangerId, 'orderNo' => 1]],
        );

        $this->repository->shouldReceive('find')
            ->withArgs(fn (SongId $arg): bool => $arg->value === $songId)
            ->andReturn($song)
            ->once();

        $this->assembler->shouldReceive('assemble')
            ->with($song)
            ->andReturn(
                new AssembledSong(
                    $song->songId->value,
                    $song->title->value,
                    $song->description->value,
                    $song->songType->getName(),
                    $song->songType->value,
                    $song->orderNo->value,
                    [new AssembledCreator($lyricistId, '作詞者A', 1)],
                    [new AssembledCreator($composerId, '作曲者A', 1)],
                    [new AssembledCreator($arrangerId, '編曲者A', 1)],
                ),
            )
            ->once();

        $result = $this->getInstance()->handle(new GetInputData($songId));

        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertSame($songId, $response->song->songId);
        $this->assertSame('描き続けた君へ', $response->song->title);
        $this->assertSame('オリジナル楽曲', $response->song->description);
        $this->assertSame(SongType::Original->getName(), $response->song->songTypeName);
        $this->assertSame(SongType::Original->value, $response->song->songTypeValue);
        $this->assertSame(1, $response->song->orderNo);

        $this->assertCount(1, $response->song->lyricists);
        $this->assertSame($lyricistId, $response->song->lyricists[0]->creatorId);
        $this->assertSame('作詞者A', $response->song->lyricists[0]->name);

        $this->assertCount(1, $response->song->composers);
        $this->assertSame($composerId, $response->song->composers[0]->creatorId);
        $this->assertSame('作曲者A', $response->song->composers[0]->name);

        $this->assertCount(1, $response->song->arrangers);
        $this->assertSame($arrangerId, $response->song->arrangers[0]->creatorId);
        $this->assertSame('編曲者A', $response->song->arrangers[0]->name);
    }

    #[Test]
    public function failureGetSong(): void
    {
        $songId = 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD';

        $this->repository->shouldReceive('find')
            ->withArgs(fn (SongId $arg): bool => $arg->value === $songId)
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->handle(new GetInputData($songId));

        $this->assertFalse($result->isOk());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(NotFoundError::class, $error);
        $this->assertSame('楽曲', $error->resourceName);
        $this->assertSame($songId, $error->identifier);
    }

    private function getInstance(): GetInteractor
    {
        return new GetInteractor(
            $this->privilegedContext(),
            $this->repository,
            $this->assembler,
        );
    }
}
