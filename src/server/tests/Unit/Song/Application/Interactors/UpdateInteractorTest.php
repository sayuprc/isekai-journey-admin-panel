<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Interactors;

use Closure;
use Mockery;
use Mockery\MockInterface;
use Override;
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
use Song\Domain\Models\SongType;
use Song\Domain\Services\SongIntegrityService;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\DomainValidationError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class UpdateInteractorTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private MockInterface&SongRepositoryInterface $repository;

    private MockInterface&SongIntegrityService $service;

    private MockInterface&SongAssembler $assembler;

    #[Override]
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
        $typeValue = SongType::Original->value;
        $isDisplay = false;
        $orderNo = 1;
        $lyricists = [['creatorId' => $lyricistId = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'orderNo' => 1]];
        $composers = [['creatorId' => $composerId = 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', 'orderNo' => 1]];
        $arrangers = [['creatorId' => $arrangerId = 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', 'orderNo' => 1]];

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->service->shouldReceive('prepareForUpdate')
            ->with($songId, $title, $description, $typeValue, $isDisplay, $orderNo, [], $lyricists, $composers, $arrangers)
            ->andReturn(
                new Ok($song = $this->createSong(
                    $songId,
                    $title,
                    $description,
                    SongType::from($typeValue),
                    $isDisplay,
                    $orderNo,
                    [],
                    $lyricists,
                    $composers,
                    $arrangers,
                )),
            )
            ->once();

        $this->repository->shouldReceive('save')
            ->withArgs(
                fn (Song $arg): bool => $arg->songId->value === $songId
                    && $arg->title->value === $title
                    && $arg->description->value === $description
                    && $arg->type->value === $typeValue
                    && $arg->isDisplay === $isDisplay
                    && $arg->orderNo->value === $orderNo
                    && $arg->lyricists->count() === 1
                    && $arg->lyricists[0]->creatorId->value === $lyricistId
                    && $arg->lyricists[0]->orderNo->value === 1
                    && $arg->composers->count() === 1
                    && $arg->composers[0]->creatorId->value === $composerId
                    && $arg->composers[0]->orderNo->value === 1
                    && $arg->arrangers->count() === 1
                    && $arg->arrangers[0]->creatorId->value === $arrangerId
                    && $arg->arrangers[0]->orderNo->value === 1,
            )
            ->andReturn($song)
            ->once();

        $this->assembler->shouldReceive('assemble')
            ->withArgs(
                fn (Song $arg): bool => $arg->songId->value === $songId
                    && $arg->title->value === $title
                    && $arg->description->value === $description
                    && $arg->type->value === $typeValue
                    && $arg->isDisplay === $isDisplay
                    && $arg->orderNo->value === $orderNo
                    && $arg->lyricists->count() === 1
                    && $arg->lyricists[0]->creatorId->value === $lyricistId
                    && $arg->lyricists[0]->orderNo->value === 1
                    && $arg->composers->count() === 1
                    && $arg->composers[0]->creatorId->value === $composerId
                    && $arg->composers[0]->orderNo->value === 1
                    && $arg->arrangers->count() === 1
                    && $arg->arrangers[0]->creatorId->value === $arrangerId
                    && $arg->arrangers[0]->orderNo->value === 1,
            )
            ->andReturn(
                new AssembledSong(
                    $song->songId->value,
                    $song->title->value,
                    $song->description->value,
                    $song->type->name,
                    $song->type->value,
                    $song->isDisplay,
                    $song->orderNo->value,
                    [new AssembledCreator($lyricistId, '作詞者', 1)],
                    [new AssembledCreator($composerId, '作曲者', 1)],
                    [new AssembledCreator($arrangerId, '編曲者', 1)],
                ),
            )
            ->once();

        $result = $this->getInstance()->handle(
            new UpdateInputData(
                $songId,
                $title,
                $description,
                $typeValue,
                $isDisplay,
                $orderNo,
                [],
                $lyricists,
                $composers,
                $arrangers,
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
        $typeValue = 1;
        $isDisplay = false;
        $orderNo = 1;
        $lyricists = [['creatorId' => 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'orderNo' => 1]];
        $composers = [['creatorId' => 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', 'orderNo' => 1]];
        $arrangers = [['creatorId' => 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', 'orderNo' => 1]];

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->service->shouldReceive('prepareForUpdate')
            ->with($songId, $title, $description, $typeValue, $isDisplay, $orderNo, [], $lyricists, $composers, $arrangers)
            ->andReturn(new Err(new DomainValidationError([])))
            ->once();

        $result = $this->getInstance()->handle(
            new UpdateInputData(
                $songId,
                $title,
                $description,
                $typeValue,
                $isDisplay,
                $orderNo,
                [],
                $lyricists,
                $composers,
                $arrangers,
            ),
        );

        $this->assertTrue($result->isErr());
    }

    private function getInstance(): UpdateInteractor
    {
        return new UpdateInteractor(
            $this->privilegedContext(),
            $this->transaction,
            $this->repository,
            $this->service,
            $this->assembler,
        );
    }
}
