<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Interactors;

use Creator\Domain\Models\CreatorId;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Interactors\CreateInteractor;
use Song\Application\UseCase\Create\CreateInputData;
use Song\Application\UseCase\Create\CreateUseCaseInterface;
use Song\Domain\Dtos\CreateCreatorData;
use Song\Domain\Models\Creators\Arranger;
use Song\Domain\Models\Creators\Composer;
use Song\Domain\Models\Creators\Lyricist;
use Song\Domain\Models\Description;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongFactoryInterface;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\Title;
use SongType\Domain\Models\SongTypeId;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class CreateInteractorTest extends TestCase
{
    private MockInterface&SongRepositoryInterface $repository;

    private MockInterface&SongFactoryInterface $factory;

    private CreateInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(SongRepositoryInterface::class);
        $this->factory = Mockery::mock(SongFactoryInterface::class);

        $this->interactor = new CreateInteractor($this->repository, $this->factory);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(CreateUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function create(): void
    {
        $this->factory->shouldReceive('create')
            ->with(
                'title',
                'description',
                'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
                1,
                Mockery::on(
                    fn (array $args): bool => count($args) === 1
                        && $args[0] instanceof CreateCreatorData
                        && $args[0]->creatorId === 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC'
                        && $args[0]->orderNo === 1
                ),
                Mockery::on(
                    fn (array $args): bool => count($args) === 1
                        && $args[0] instanceof CreateCreatorData
                        && $args[0]->creatorId === 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD'
                        && $args[0]->orderNo === 1
                ),
                Mockery::on(
                    fn (array $args): bool => count($args) === 1
                        && $args[0] instanceof CreateCreatorData
                        && $args[0]->creatorId === 'EEEEEEEE-EEEE-EEEE-EEEE-EEEEEEEEEEEE'
                        && $args[0]->orderNo === 1
                ),
            )
            ->andReturn(
                new Song(
                    new SongId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                    new Title('title'),
                    new Description('description'),
                    new SongTypeId('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'),
                    new OrderNo(1),
                    [
                        new Lyricist(
                            new CreatorId('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC'),
                            new OrderNo(1),
                        ),
                    ],
                    [
                        new Composer(
                            new CreatorId('DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD'),
                            new OrderNo(1),
                        ),
                    ],
                    [
                        new Arranger(
                            new CreatorId('EEEEEEEE-EEEE-EEEE-EEEE-EEEEEEEEEEEE'),
                            new OrderNo(1),
                        ),
                    ],
                )
            )
            ->once();

        $this->repository->shouldReceive('insert')
            ->with(Mockery::on(
                fn (Song $arg): bool => $arg->songId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->title->value === 'title'
                    && $arg->description->value === 'description'
                    && $arg->songTypeId->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'
                    && $arg->orderNo->value === 1
                    && count($arg->lyricists) === 1
                    && $arg->lyricists[0]->creatorId->value === 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC'
                    && $arg->lyricists[0]->orderNo->value === 1
                    && count($arg->composers) === 1
                    && $arg->composers[0]->creatorId->value === 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD'
                    && $arg->composers[0]->orderNo->value === 1
                    && count($arg->arrangers) === 1
                    && $arg->arrangers[0]->creatorId->value === 'EEEEEEEE-EEEE-EEEE-EEEE-EEEEEEEEEEEE'
                    && $arg->arrangers[0]->orderNo->value === 1
            ))
            ->once();

        $this->interactor->handle(new CreateInputData(
            'title',
            'description',
            'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
            1,
            [
                new CreateCreatorData('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', 1),
            ],
            [
                new CreateCreatorData('DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', 1),
            ],
            [
                new CreateCreatorData('EEEEEEEE-EEEE-EEEE-EEEE-EEEEEEEEEEEE', 1),
            ],
        ));
    }
}
