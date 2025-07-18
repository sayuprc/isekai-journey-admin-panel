<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Infrastructures;

use DateType\ImmutableDate;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Dtos\CreateCreatorData;
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
            ->once();

        $song = $this->factory->create(
            'title',
            'description',
            new ImmutableDate('2019-12-12'),
            'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
            [
                new CreateCreatorData('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', 1),
            ],
            [
                new CreateCreatorData('DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', 1),
            ],
            [
                new CreateCreatorData('EEEEEEEE-EEEE-EEEE-EEEE-EEEEEEEEEEEE', 1),
            ],
        );

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $song->songId->value);
        $this->assertSame('title', $song->title->value);
        $this->assertSame('description', $song->description->value);
        $this->assertSame('2019-12-12', $song->releasedOn->value->format('Y-m-d'));
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $song->songTypeId->value);
        $this->assertCount(1, $song->lyricists);
        $this->assertSame('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', $song->lyricists[0]->creatorId->value);
        $this->assertSame(1, $song->lyricists[0]->orderNo->value);
        $this->assertCount(1, $song->composers);
        $this->assertSame('DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', $song->composers[0]->creatorId->value);
        $this->assertSame(1, $song->composers[0]->orderNo->value);
        $this->assertCount(1, $song->arrangers);
        $this->assertSame('EEEEEEEE-EEEE-EEEE-EEEE-EEEEEEEEEEEE', $song->arrangers[0]->creatorId->value);
        $this->assertSame(1, $song->arrangers[0]->orderNo->value);
    }
}
