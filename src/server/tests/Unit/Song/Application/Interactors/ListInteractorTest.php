<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Interactors;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Assemble\AssembledCreator;
use Song\Application\Assemble\AssembledSong;
use Song\Application\Assemble\SongAssembler;
use Song\Application\Interactors\ListInteractor;
use Song\Domain\Models\SongRepositoryInterface;
use SongType\Domain\Models\SongType;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class ListInteractorTest extends TestCase
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
    public function emptySongs(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([])
            ->once();

        $result = $this->getInstance()->handle();
        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertCount(0, $response->songs);
    }

    #[Test]
    public function nonEmptySongs(): void
    {
        $arrangerId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $composerId = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB';
        $lyricistId = 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC';

        $song1 = $this->createSong(
            'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD',
            '描き続けた君へ',
            'オリジナル楽曲',
            SongType::Original,
            1,
            [['creatorId' => $arrangerId, 'orderNo' => 1]],
            [['creatorId' => $composerId, 'orderNo' => 1]],
            [['creatorId' => $lyricistId, 'orderNo' => 1]],
        );
        $song2 = $this->createSong(
            'EEEEEEEE-EEEE-EEEE-EEEE-EEEEEEEEEEEE',
            '全部夢だった！',
            'カバー楽曲',
            SongType::Cover,
            2,
            [['creatorId' => $arrangerId, 'orderNo' => 1]],
            [['creatorId' => $composerId, 'orderNo' => 1]],
            [],
        );

        $this->repository->shouldReceive('all')
            ->andReturn([$song1, $song2])
            ->once();

        $this->assembler->shouldReceive('assemble')
            ->with($song1)
            ->andReturn(
                new AssembledSong(
                    $song1->songId->value,
                    $song1->title->value,
                    $song1->description->value,
                    $song1->songType->getName(),
                    $song1->songType->value,
                    $song1->orderNo->value,
                    [new AssembledCreator($arrangerId, '編曲者A', 1)],
                    [new AssembledCreator($composerId, '作曲者A', 1)],
                    [new AssembledCreator($lyricistId, '作詞者A', 1)],
                ),
            )
            ->once();

        $this->assembler->shouldReceive('assemble')
            ->with($song2)
            ->andReturn(
                new AssembledSong(
                    $song2->songId->value,
                    $song2->title->value,
                    $song2->description->value,
                    $song2->songType->getName(),
                    $song2->songType->value,
                    $song2->orderNo->value,
                    [new AssembledCreator($arrangerId, '編曲者A', 1)],
                    [new AssembledCreator($composerId, '作曲者A', 1)],
                    [],
                ),
            )
            ->once();

        $result = $this->getInstance()->handle();
        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertCount(2, $response->songs);

        $this->assertSame('DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', $response->songs[0]->songId);
        $this->assertSame('描き続けた君へ', $response->songs[0]->title);
        $this->assertSame(SongType::Original->getName(), $response->songs[0]->songTypeName);
        $this->assertSame(SongType::Original->value, $response->songs[0]->songTypeValue);
        $this->assertSame(1, $response->songs[0]->orderNo);
        $this->assertCount(1, $response->songs[0]->arrangers);
        $this->assertSame($arrangerId, $response->songs[0]->arrangers[0]->creatorId);
        $this->assertSame('編曲者A', $response->songs[0]->arrangers[0]->creatorName);
        $this->assertSame(1, $response->songs[0]->arrangers[0]->orderNo);
        $this->assertCount(1, $response->songs[0]->composers);
        $this->assertSame($composerId, $response->songs[0]->composers[0]->creatorId);
        $this->assertSame('作曲者A', $response->songs[0]->composers[0]->creatorName);
        $this->assertCount(1, $response->songs[0]->lyricists);
        $this->assertSame($lyricistId, $response->songs[0]->lyricists[0]->creatorId);
        $this->assertSame('作詞者A', $response->songs[0]->lyricists[0]->creatorName);

        $this->assertSame('EEEEEEEE-EEEE-EEEE-EEEE-EEEEEEEEEEEE', $response->songs[1]->songId);
        $this->assertSame('全部夢だった！', $response->songs[1]->title);
        $this->assertSame(SongType::Cover->getName(), $response->songs[1]->songTypeName);
        $this->assertSame(SongType::Cover->value, $response->songs[1]->songTypeValue);
        $this->assertSame(2, $response->songs[1]->orderNo);
        $this->assertCount(1, $response->songs[1]->arrangers);
        $this->assertSame($arrangerId, $response->songs[1]->arrangers[0]->creatorId);
        $this->assertSame('編曲者A', $response->songs[1]->arrangers[0]->creatorName);
        $this->assertCount(1, $response->songs[1]->composers);
        $this->assertSame($composerId, $response->songs[1]->composers[0]->creatorId);
        $this->assertSame('作曲者A', $response->songs[1]->composers[0]->creatorName);
        $this->assertCount(0, $response->songs[1]->lyricists);
    }

    private function getInstance(): ListInteractor
    {
        return new ListInteractor(
            $this->repository,
            $this->assembler,
        );
    }
}
