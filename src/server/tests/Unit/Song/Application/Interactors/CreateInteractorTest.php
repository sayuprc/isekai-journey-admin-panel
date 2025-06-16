<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Interactors;

use Creator\Domain\Models\CreatorId;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Safe\DateTimeImmutable;
use Song\Application\Interactors\CreateInteractor;
use Song\Application\UseCase\Create\CreateInputData;
use Song\Application\UseCase\Create\CreateUseCaseInterface;
use Song\Domain\Dtos\CreateCreatorData;
use Song\Domain\Dtos\CreateNonLinkArchiveData;
use Song\Domain\Dtos\CreateTwitterArchiveData;
use Song\Domain\Dtos\CreateYouTubeArchiveData;
use Song\Domain\Models\Archives\ArchivedOn;
use Song\Domain\Models\Archives\ArchiveId;
use Song\Domain\Models\Archives\ArchiveName;
use Song\Domain\Models\Archives\NonLink\NonLinkArchive;
use Song\Domain\Models\Archives\Twitter\PostUrl;
use Song\Domain\Models\Archives\Twitter\TwitterArchive;
use Song\Domain\Models\Archives\YouTube\ThumbnailUrl;
use Song\Domain\Models\Archives\YouTube\VideoUrl;
use Song\Domain\Models\Archives\YouTube\YouTubeArchive;
use Song\Domain\Models\Creators\Arranger;
use Song\Domain\Models\Creators\Composer;
use Song\Domain\Models\Creators\Lyricist;
use Song\Domain\Models\Description;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongFactoryInterface;
use Song\Domain\Models\SongId;
use Song\Domain\Models\Title;
use Song\Domain\Repositories\SongRepositoryInterface;
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
                Mockery::on(
                    fn (array $args): bool => count($args) === 3
                        && $args[0] instanceof CreateYouTubeArchiveData
                        && $args[0]->archiveName === 'YouTube'
                        && $args[0]->videoUrl === 'https://example.com'
                        && $args[0]->thumbnailUrl === 'https://example.com/thumbnail.jpg'
                        && $args[0]->archivedOn->format('Y-m-d') === '2024-08-07'
                        && $args[0]->orderNo === 1
                        && $args[1] instanceof CreateTwitterArchiveData
                        && $args[1]->archiveName === 'Tweet'
                        && $args[1]->postUrl === 'https://example.com'
                        && $args[1]->archivedOn->format('Y-m-d') === '2024-08-07'
                        && $args[1]->orderNo === 2
                        && $args[2] instanceof CreateNonLinkArchiveData
                        && $args[2]->archivedOn->format('Y-m-d') === '2024-08-07'
                        && $args[2]->orderNo === 3
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
                    [
                        new YouTubeArchive(
                            new ArchiveId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                            new ArchiveName('YouTube'),
                            new VideoUrl('https://example.com'),
                            new ThumbnailUrl('https://example.com/thumbnail.jpg'),
                            new ArchivedOn(new DateTimeImmutable('2024-08-07')),
                            new OrderNo(1),
                        ),
                        new TwitterArchive(
                            new ArchiveId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                            new ArchiveName('Twitter'),
                            new PostUrl('https://example.com'),
                            new ArchivedOn(new DateTimeImmutable('2024-08-07')),
                            new OrderNo(2),
                        ),
                        new NonLinkArchive(
                            new ArchiveId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                            new ArchivedOn(new DateTimeImmutable('2024-08-07')),
                            new OrderNo(3),
                        ),
                    ]
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
                    && count($arg->archives) === 3
                    && $arg->archives[0] instanceof YouTubeArchive
                    && $arg->archives[0]->archiveId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->archives[0]->archiveName->value === 'YouTube'
                    && $arg->archives[0]->videoUrl->value === 'https://example.com'
                    && $arg->archives[0]->thumbnailUrl->value === 'https://example.com/thumbnail.jpg'
                    && $arg->archives[0]->archivedOn->value->format('Y-m-d') === '2024-08-07'
                    && $arg->archives[0]->orderNo->value === 1
                    && $arg->archives[1] instanceof TwitterArchive
                    && $arg->archives[1]->archiveId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->archives[1]->archiveName->value === 'Twitter'
                    && $arg->archives[1]->postUrl->value === 'https://example.com'
                    && $arg->archives[1]->archivedOn->value->format('Y-m-d') === '2024-08-07'
                    && $arg->archives[1]->orderNo->value === 2
                    && $arg->archives[2] instanceof NonLinkArchive
                    && $arg->archives[2]->archiveId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->archives[2]->archivedOn->value->format('Y-m-d') === '2024-08-07'
                    && $arg->archives[2]->orderNo->value === 3
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
            [
                new CreateYouTubeArchiveData(
                    'YouTube',
                    'https://example.com',
                    'https://example.com/thumbnail.jpg',
                    new DateTimeImmutable('2024-08-07'),
                    1,
                ),
                new CreateTwitterArchiveData(
                    'Tweet',
                    'https://example.com',
                    new DateTimeImmutable('2024-08-07'),
                    2
                ),
                new CreateNonLinkArchiveData(
                    new DateTimeImmutable('2024-08-07'),
                    3
                ),
            ],
        ));
    }
}
