<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Infrastructures;

use DateType\ImmutableDate;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Dtos\CreateCreatorData;
use Song\Domain\Dtos\CreateNonLinkArchiveData;
use Song\Domain\Dtos\CreateTwitterArchiveData;
use Song\Domain\Dtos\CreateYouTubeArchiveData;
use Song\Domain\Models\Archives\NonLink\NonLinkArchive;
use Song\Domain\Models\Archives\Twitter\TwitterArchive;
use Song\Domain\Models\Archives\YouTube\YouTubeArchive;
use Song\Infrastructures\SongFactory;
use Support\Contracts\UuidGeneratorInterface;
use Tests\TestCase;

class SongFactoryTest extends TestCase
{
    private MockInterface&UuidGeneratorInterface $uuid;

    private SongFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uuid = Mockery::mock(UuidGeneratorInterface::class);

        $this->factory = new SongFactory($this->uuid);
    }

    #[Test]
    public function create(): void
    {
        $this->uuid->shouldReceive('generate')
            ->andReturn('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->times(4);

        $song = $this->factory->create(
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
                    new ImmutableDate('2024-08-07'),
                    1,
                ),
                new CreateTwitterArchiveData(
                    'Tweet',
                    'https://example.com',
                    new ImmutableDate('2024-08-07'),
                    2
                ),
                new CreateNonLinkArchiveData(
                    new ImmutableDate('2024-08-07'),
                    3
                ),
            ],
        );

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $song->songId->value);
        $this->assertSame('title', $song->title->value);
        $this->assertSame('description', $song->description->value);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $song->songTypeId->value);
        $this->assertSame(1, $song->orderNo->value);
        $this->assertCount(1, $song->lyricists);
        $this->assertSame('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', $song->lyricists[0]->creatorId->value);
        $this->assertSame(1, $song->lyricists[0]->orderNo->value);
        $this->assertCount(1, $song->composers);
        $this->assertSame('DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', $song->composers[0]->creatorId->value);
        $this->assertSame(1, $song->composers[0]->orderNo->value);
        $this->assertCount(1, $song->arrangers);
        $this->assertSame('EEEEEEEE-EEEE-EEEE-EEEE-EEEEEEEEEEEE', $song->arrangers[0]->creatorId->value);
        $this->assertSame(1, $song->arrangers[0]->orderNo->value);
        $this->assertCount(3, $song->archives);
        $youtube = $song->archives[0];
        $this->assertInstanceOf(YouTubeArchive::class, $youtube);
        $this->assertSame('YouTube', $youtube->archiveName->value);
        $this->assertSame('https://example.com', $youtube->videoUrl->value);
        $this->assertSame('https://example.com/thumbnail.jpg', $youtube->thumbnailUrl->value);
        $this->assertSame('2024-08-07', $youtube->archivedOn->value->format('Y-m-d'));
        $this->assertSame(1, $youtube->orderNo->value);
        $twitter = $song->archives[1];
        $this->assertInstanceOf(TwitterArchive::class, $twitter);
        $this->assertSame('Tweet', $twitter->archiveName->value);
        $this->assertSame('https://example.com', $twitter->postUrl->value);
        $this->assertSame('2024-08-07', $twitter->archivedOn->value->format('Y-m-d'));
        $this->assertSame(2, $twitter->orderNo->value);
        $non = $song->archives[2];
        $this->assertInstanceOf(NonLinkArchive::class, $non);
        $this->assertSame('2024-08-07', $non->archivedOn->value->format('Y-m-d'));
        $this->assertSame(3, $non->orderNo->value);
    }
}
