<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Interactors;

use Creator\Domain\Models\CreatorId;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Interactors\ListInteractor;
use Song\Application\UseCase\List\ListOutputData;
use Song\Application\UseCase\List\ListUseCaseInterface;
use Song\Domain\Models\Creators\Arranger;
use Song\Domain\Models\Creators\Composer;
use Song\Domain\Models\Creators\Lyricist;
use Song\Domain\Models\Description;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\Title;
use SongType\Domain\Models\SongTypeId;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class ListInteractorTest extends TestCase
{
    private MockInterface&SongRepositoryInterface $repository;

    private ListInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(SongRepositoryInterface::class);

        $this->interactor = new ListInteractor($this->repository);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(ListUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function emptySongs(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([])
            ->once();

        $response = $this->interactor->handle();

        $this->assertInstanceOf(ListOutputData::class, $response);

        $this->assertCount(0, $response->songs);
    }

    #[Test]
    public function nonEmptySongs(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([
                new Song(
                    new SongId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                    new Title('楽曲A'),
                    new Description('楽曲Aの説明'),
                    new SongTypeId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAB'),
                    new OrderNo(1),
                    [
                        new Lyricist(
                            new CreatorId('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'),
                            new OrderNo(1)
                        ),
                    ],
                    [
                        new Composer(
                            new CreatorId('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC'),
                            new OrderNo(1)
                        ),
                    ],
                    [
                        new Arranger(
                            new CreatorId('DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD'),
                            new OrderNo(1)
                        ),
                    ],
                ),
                new Song(
                    new SongId('00000000-0000-0000-0000-000000000000'),
                    new Title('楽曲B'),
                    new Description('楽曲Bの説明'),
                    new SongTypeId('11111111-1111-1111-1111-111111111111'),
                    new OrderNo(2),
                    [
                        new Lyricist(
                            new CreatorId('22222222-2222-2222-2222-222222222222'),
                            new OrderNo(1)
                        ),
                    ],
                    [
                        new Composer(
                            new CreatorId('33333333-3333-3333-3333-333333333333'),
                            new OrderNo(1)
                        ),
                    ],
                    [
                        new Arranger(
                            new CreatorId('44444444-4444-4444-4444-444444444444'),
                            new OrderNo(1)
                        ),
                    ],
                ),
                new Song(
                    new SongId('66666666-6666-6666-6666-666666666666'),
                    new Title('楽曲C'),
                    new Description('楽曲Cの説明'),
                    new SongTypeId('77777777-7777-7777-7777-777777777777'),
                    new OrderNo(3),
                    [
                        new Lyricist(
                            new CreatorId('88888888-8888-8888-8888-888888888888'),
                            new OrderNo(1)
                        ),
                    ],
                    [
                        new Composer(
                            new CreatorId('99999999-9999-9999-9999-999999999999'),
                            new OrderNo(1)
                        ),
                    ],
                    [
                        new Arranger(
                            new CreatorId('10101010-1010-1010-1010-101010101010'),
                            new OrderNo(1)
                        ),
                    ],
                ),
            ])
            ->once();

        $response = $this->interactor->handle();

        $this->assertInstanceOf(ListOutputData::class, $response);

        $this->assertCount(3, $response->songs);

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $response->songs[0]->songId->value);
        $this->assertSame('楽曲A', $response->songs[0]->title->value);
        $this->assertSame('楽曲Aの説明', $response->songs[0]->description->value);
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAB', $response->songs[0]->songTypeId->value);
        $this->assertCount(1, $response->songs[0]->lyricists);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $response->songs[0]->lyricists[0]->creatorId->value);
        $this->assertSame(1, $response->songs[0]->lyricists[0]->orderNo->value);
        $this->assertCount(1, $response->songs[0]->composers);
        $this->assertSame('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', $response->songs[0]->composers[0]->creatorId->value);
        $this->assertSame(1, $response->songs[0]->composers[0]->orderNo->value);
        $this->assertCount(1, $response->songs[0]->arrangers);
        $this->assertSame('DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', $response->songs[0]->arrangers[0]->creatorId->value);
        $this->assertSame(1, $response->songs[0]->arrangers[0]->orderNo->value);
        $this->assertSame(1, $response->songs[0]->orderNo->value);

        $this->assertSame('00000000-0000-0000-0000-000000000000', $response->songs[1]->songId->value);
        $this->assertSame('楽曲B', $response->songs[1]->title->value);
        $this->assertSame('楽曲Bの説明', $response->songs[1]->description->value);
        $this->assertSame('11111111-1111-1111-1111-111111111111', $response->songs[1]->songTypeId->value);
        $this->assertCount(1, $response->songs[1]->lyricists);
        $this->assertSame('22222222-2222-2222-2222-222222222222', $response->songs[1]->lyricists[0]->creatorId->value);
        $this->assertSame(1, $response->songs[1]->lyricists[0]->orderNo->value);
        $this->assertCount(1, $response->songs[1]->composers);
        $this->assertSame('33333333-3333-3333-3333-333333333333', $response->songs[1]->composers[0]->creatorId->value);
        $this->assertSame(1, $response->songs[1]->composers[0]->orderNo->value);
        $this->assertCount(1, $response->songs[1]->arrangers);
        $this->assertSame('44444444-4444-4444-4444-444444444444', $response->songs[1]->arrangers[0]->creatorId->value);
        $this->assertSame(1, $response->songs[1]->arrangers[0]->orderNo->value);
        $this->assertSame(2, $response->songs[1]->orderNo->value);

        $this->assertSame('66666666-6666-6666-6666-666666666666', $response->songs[2]->songId->value);
        $this->assertSame('楽曲C', $response->songs[2]->title->value);
        $this->assertSame('楽曲Cの説明', $response->songs[2]->description->value);
        $this->assertSame('77777777-7777-7777-7777-777777777777', $response->songs[2]->songTypeId->value);
        $this->assertCount(1, $response->songs[2]->lyricists);
        $this->assertSame('88888888-8888-8888-8888-888888888888', $response->songs[2]->lyricists[0]->creatorId->value);
        $this->assertSame(1, $response->songs[2]->lyricists[0]->orderNo->value);
        $this->assertCount(1, $response->songs[2]->composers);
        $this->assertSame('99999999-9999-9999-9999-999999999999', $response->songs[2]->composers[0]->creatorId->value);
        $this->assertSame(1, $response->songs[2]->composers[0]->orderNo->value);
        $this->assertCount(1, $response->songs[2]->arrangers);
        $this->assertSame('10101010-1010-1010-1010-101010101010', $response->songs[2]->arrangers[0]->creatorId->value);
        $this->assertSame(1, $response->songs[2]->arrangers[0]->orderNo->value);
        $this->assertSame(3, $response->songs[2]->orderNo->value);
    }
}
