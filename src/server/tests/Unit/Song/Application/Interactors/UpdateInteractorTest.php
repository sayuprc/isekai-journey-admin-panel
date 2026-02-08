<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Interactors;

use Closure;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use ResultType\Err;
use ResultType\Ok;
use Song\Application\Assemble\AssembledCreator;
use Song\Application\Assemble\AssembledSong;
use Song\Application\Assemble\SongAssembler;
use Song\Application\Interactors\UpdateInteractor;
use Song\Application\UseCase\Update\UpdateInputData;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Services\SongIntegrityService;
use SongType\Domain\Models\SongType;
use Support\Contracts\TransactionInterface;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class UpdateInteractorTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private MockInterface&SongRepositoryInterface $repository;

    private MockInterface&SongIntegrityService $service;

    private MockInterface&SongAssembler $assembler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->repository = Mockery::mock(SongRepositoryInterface::class);
        $this->service = Mockery::mock(SongIntegrityService::class);
        $this->assembler = Mockery::mock(SongAssembler::class);
    }

    #[Test]
    public function update(): void
    {
        $songId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $title = '描き続けた君へ';
        $description = 'オリジナル楽曲';
        $songTypeValue = SongType::Original->value;
        $orderNo = 1;
        $arrangers = [['creatorId' => $arrangerId = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'orderNo' => 1]];
        $composers = [['creatorId' => $composerId = 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', 'orderNo' => 1]];
        $lyricists = [['creatorId' => $lyricistId = 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', 'orderNo' => 1]];

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->service->shouldReceive('prepareForUpdate')
            ->with($songId, $title, $description, $songTypeValue, $orderNo, $arrangers, $composers, $lyricists)
            ->andReturn(
                new Ok($song = $this->createSong(
                    $songId,
                    $title,
                    $description,
                    SongType::from($songTypeValue),
                    $orderNo,
                    $arrangers,
                    $composers,
                    $lyricists,
                )),
            )
            ->once();

        $this->repository->shouldReceive('save')
            ->withArgs(
                fn (Song $arg): bool => $arg->songId->value === $songId
                    && $arg->title->value === $title
                    && $arg->description->value === $description
                    && $arg->songType->value === $songTypeValue
                    && $arg->orderNo->value === $orderNo
                    && $arg->arrangers->count() === 1
                    && $arg->arrangers[0]->creatorId->value === $arrangerId
                    && $arg->arrangers[0]->orderNo->value === 1
                    && $arg->composers->count() === 1
                    && $arg->composers[0]->creatorId->value === $composerId
                    && $arg->composers[0]->orderNo->value === 1
                    && $arg->lyricists->count() === 1
                    && $arg->lyricists[0]->creatorId->value === $lyricistId
                    && $arg->lyricists[0]->orderNo->value === 1,
            )
            ->andReturn($song)
            ->once();

        $this->assembler->shouldReceive('assemble')
            ->withArgs(
                fn (Song $arg): bool => $arg->songId->value === $songId
                    && $arg->title->value === $title
                    && $arg->description->value === $description
                    && $arg->songType->value === $songTypeValue
                    && $arg->orderNo->value === $orderNo
                    && $arg->arrangers->count() === 1
                    && $arg->arrangers[0]->creatorId->value === $arrangerId
                    && $arg->arrangers[0]->orderNo->value === 1
                    && $arg->composers->count() === 1
                    && $arg->composers[0]->creatorId->value === $composerId
                    && $arg->composers[0]->orderNo->value === 1
                    && $arg->lyricists->count() === 1
                    && $arg->lyricists[0]->creatorId->value === $lyricistId
                    && $arg->lyricists[0]->orderNo->value === 1,
            )
            ->andReturn(
                new AssembledSong(
                    $song->songId->value,
                    $song->title->value,
                    $song->description->value,
                    $song->songType->name,
                    $song->songType->value,
                    $song->orderNo->value,
                    [new AssembledCreator($arrangerId, '編曲者', 1)],
                    [new AssembledCreator($composerId, '作曲者', 1)],
                    [new AssembledCreator($lyricistId, '作詞者', 1)],
                ),
            )
            ->once();

        $result = $this->getInstance()->handle(
            new UpdateInputData(
                $songId,
                $title,
                $description,
                $songTypeValue,
                $orderNo,
                $arrangers,
                $composers,
                $lyricists,
            ),
        );

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function updateFailsIfCreatorIdNotExists(): void
    {
        $songId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $title = '曲名';
        $description = '説明';
        $songTypeValue = 1;
        $orderNo = 1;
        $arrangers = [['creatorId' => 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'orderNo' => 1]];
        $composers = [['creatorId' => 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', 'orderNo' => 1]];
        $lyricists = [['creatorId' => 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', 'orderNo' => 1]];

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->service->shouldReceive('prepareForUpdate')
            ->with($songId, $title, $description, $songTypeValue, $orderNo, $arrangers, $composers, $lyricists)
            ->andReturn(new Err(''))
            ->once();

        $result = $this->getInstance()->handle(
            new UpdateInputData(
                $songId,
                $title,
                $description,
                $songTypeValue,
                $orderNo,
                $arrangers,
                $composers,
                $lyricists,
            ),
        );

        $this->assertTrue($result->isErr());
    }

    private function getInstance(): UpdateInteractor
    {
        return new UpdateInteractor(
            $this->transaction,
            $this->repository,
            $this->service,
            $this->assembler,
        );
    }
}
