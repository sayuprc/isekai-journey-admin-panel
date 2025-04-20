<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLog\Infrastructures\Factories;

use DateTimeImmutable;
use JourneyLog\Domain\Dtos\CreateJourneyLogLinkData;
use JourneyLog\Domain\Dtos\ReconstituteJourneyLogLinkData;
use JourneyLog\Infrastructures\Factories\JourneyLogFactory;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Uuid\UuidGeneratorInterface;
use Tests\TestCase;

class JourneyLogFactoryTest extends TestCase
{
    private MockInterface&UuidGeneratorInterface $uuid;

    private JourneyLogFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uuid = Mockery::mock(UuidGeneratorInterface::class);

        $this->factory = new JourneyLogFactory($this->uuid);
    }

    #[Test]
    public function create(): void
    {
        $this->uuid->shouldReceive('generate')
            ->andReturn('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->once();

        $journeyLog = $this->factory->create(
            'story',
            new DateTimeImmutable('2019-12-08'),
            new DateTimeImmutable('2019-12-09'),
            1,
            []
        );

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $journeyLog->journeyLogId->value);
        $this->assertSame('story', $journeyLog->story->value);
        $this->assertSame('2019-12-08', $journeyLog->period->fromOn->value->format('Y-m-d'));
        $this->assertSame('2019-12-09', $journeyLog->period->toOn->value->format('Y-m-d'));
        $this->assertSame(1, $journeyLog->orderNo->value);
        $this->assertCount(0, $journeyLog->journeyLogLinks);
    }

    #[Test]
    public function createWithLinks(): void
    {
        $this->uuid->shouldReceive('generate')
            ->andReturn('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->times(3);

        $journeyLog = $this->factory->create(
            'story',
            new DateTimeImmutable('2019-12-08'),
            new DateTimeImmutable('2019-12-09'),
            1,
            [
                new CreateJourneyLogLinkData(
                    'リンク1',
                    'https://example.com/1',
                    1,
                    'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
                ),
                new CreateJourneyLogLinkData(
                    'リンク2',
                    'https://example.com/2',
                    2,
                    'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC',
                ),
            ]
        );

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $journeyLog->journeyLogId->value);
        $this->assertSame('story', $journeyLog->story->value);
        $this->assertSame('2019-12-08', $journeyLog->period->fromOn->value->format('Y-m-d'));
        $this->assertSame('2019-12-09', $journeyLog->period->toOn->value->format('Y-m-d'));
        $this->assertSame(1, $journeyLog->orderNo->value);
        $this->assertCount(2, $journeyLog->journeyLogLinks);
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $journeyLog->journeyLogLinks[0]->journeyLogLinkId->value);
        $this->assertSame('リンク1', $journeyLog->journeyLogLinks[0]->journeyLogLinkName->value);
        $this->assertSame('https://example.com/1', $journeyLog->journeyLogLinks[0]->url->value);
        $this->assertSame(1, $journeyLog->journeyLogLinks[0]->orderNo->value);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $journeyLog->journeyLogLinks[0]->journeyLogLinkTypeId->value);
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $journeyLog->journeyLogLinks[1]->journeyLogLinkId->value);
        $this->assertSame('リンク2', $journeyLog->journeyLogLinks[1]->journeyLogLinkName->value);
        $this->assertSame('https://example.com/2', $journeyLog->journeyLogLinks[1]->url->value);
        $this->assertSame(2, $journeyLog->journeyLogLinks[1]->orderNo->value);
        $this->assertSame('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', $journeyLog->journeyLogLinks[1]->journeyLogLinkTypeId->value);
    }

    #[Test]
    public function createForUpdate(): void
    {
        $journeyLog = $this->factory->createForUpdate(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            'story',
            new DateTimeImmutable('2019-12-08'),
            new DateTimeImmutable('2019-12-09'),
            1,
            []
        );

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $journeyLog->journeyLogId->value);
        $this->assertSame('story', $journeyLog->story->value);
        $this->assertSame('2019-12-08', $journeyLog->period->fromOn->value->format('Y-m-d'));
        $this->assertSame('2019-12-09', $journeyLog->period->toOn->value->format('Y-m-d'));
        $this->assertSame(1, $journeyLog->orderNo->value);
        $this->assertCount(0, $journeyLog->journeyLogLinks);
    }

    #[Test]
    public function createForUpdateWithLinks(): void
    {
        $this->uuid->shouldReceive('generate')
            ->andReturn('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->twice();

        $journeyLog = $this->factory->createForUpdate(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            'story',
            new DateTimeImmutable('2019-12-08'),
            new DateTimeImmutable('2019-12-09'),
            1,
            [
                new CreateJourneyLogLinkData(
                    'リンク1',
                    'https://example.com/1',
                    1,
                    'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
                ),
                new CreateJourneyLogLinkData(
                    'リンク2',
                    'https://example.com/2',
                    2,
                    'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC',
                ),
            ]
        );

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $journeyLog->journeyLogId->value);
        $this->assertSame('story', $journeyLog->story->value);
        $this->assertSame('2019-12-08', $journeyLog->period->fromOn->value->format('Y-m-d'));
        $this->assertSame('2019-12-09', $journeyLog->period->toOn->value->format('Y-m-d'));
        $this->assertSame(1, $journeyLog->orderNo->value);
        $this->assertCount(2, $journeyLog->journeyLogLinks);
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $journeyLog->journeyLogLinks[0]->journeyLogLinkId->value);
        $this->assertSame('リンク1', $journeyLog->journeyLogLinks[0]->journeyLogLinkName->value);
        $this->assertSame('https://example.com/1', $journeyLog->journeyLogLinks[0]->url->value);
        $this->assertSame(1, $journeyLog->journeyLogLinks[0]->orderNo->value);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $journeyLog->journeyLogLinks[0]->journeyLogLinkTypeId->value);
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $journeyLog->journeyLogLinks[1]->journeyLogLinkId->value);
        $this->assertSame('リンク2', $journeyLog->journeyLogLinks[1]->journeyLogLinkName->value);
        $this->assertSame('https://example.com/2', $journeyLog->journeyLogLinks[1]->url->value);
        $this->assertSame(2, $journeyLog->journeyLogLinks[1]->orderNo->value);
        $this->assertSame('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', $journeyLog->journeyLogLinks[1]->journeyLogLinkTypeId->value);
    }

    #[Test]
    public function reconstitute(): void
    {
        $journeyLog = $this->factory->reconstitute(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            'story',
            new DateTimeImmutable('2019-12-08'),
            new DateTimeImmutable('2019-12-09'),
            1,
            []
        );

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $journeyLog->journeyLogId->value);
        $this->assertSame('story', $journeyLog->story->value);
        $this->assertSame('2019-12-08', $journeyLog->period->fromOn->value->format('Y-m-d'));
        $this->assertSame('2019-12-09', $journeyLog->period->toOn->value->format('Y-m-d'));
        $this->assertSame(1, $journeyLog->orderNo->value);
        $this->assertCount(0, $journeyLog->journeyLogLinks);
    }

    #[Test]
    public function reconstituteWithLinks(): void
    {
        $journeyLog = $this->factory->reconstitute(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            'story',
            new DateTimeImmutable('2019-12-08'),
            new DateTimeImmutable('2019-12-09'),
            1,
            [
                new ReconstituteJourneyLogLinkData(
                    'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD',
                    'リンク1',
                    'https://example.com/1',
                    1,
                    'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
                ),
                new ReconstituteJourneyLogLinkData(
                    'EEEEEEEE-EEEE-EEEE-EEEE-EEEEEEEEEEEE',
                    'リンク2',
                    'https://example.com/2',
                    2,
                    'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC',
                ),
            ]
        );

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $journeyLog->journeyLogId->value);
        $this->assertSame('story', $journeyLog->story->value);
        $this->assertSame('2019-12-08', $journeyLog->period->fromOn->value->format('Y-m-d'));
        $this->assertSame('2019-12-09', $journeyLog->period->toOn->value->format('Y-m-d'));
        $this->assertSame(1, $journeyLog->orderNo->value);
        $this->assertCount(2, $journeyLog->journeyLogLinks);
        $this->assertSame('DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', $journeyLog->journeyLogLinks[0]->journeyLogLinkId->value);
        $this->assertSame('リンク1', $journeyLog->journeyLogLinks[0]->journeyLogLinkName->value);
        $this->assertSame('https://example.com/1', $journeyLog->journeyLogLinks[0]->url->value);
        $this->assertSame(1, $journeyLog->journeyLogLinks[0]->orderNo->value);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $journeyLog->journeyLogLinks[0]->journeyLogLinkTypeId->value);
        $this->assertSame('EEEEEEEE-EEEE-EEEE-EEEE-EEEEEEEEEEEE', $journeyLog->journeyLogLinks[1]->journeyLogLinkId->value);
        $this->assertSame('リンク2', $journeyLog->journeyLogLinks[1]->journeyLogLinkName->value);
        $this->assertSame('https://example.com/2', $journeyLog->journeyLogLinks[1]->url->value);
        $this->assertSame(2, $journeyLog->journeyLogLinks[1]->orderNo->value);
        $this->assertSame('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', $journeyLog->journeyLogLinks[1]->journeyLogLinkTypeId->value);
    }
}
