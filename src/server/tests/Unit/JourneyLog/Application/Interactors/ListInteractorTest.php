<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLog\Application\Interactors;

use DateType\ImmutableDate;
use JourneyLog\Application\Interactors\ListInteractor;
use JourneyLog\Application\UseCase\List\ListOutputData;
use JourneyLog\Application\UseCase\List\ListUseCaseInterface;
use JourneyLog\Domain\Models\FromOn;
use JourneyLog\Domain\Models\JourneyLog;
use JourneyLog\Domain\Models\JourneyLogId;
use JourneyLog\Domain\Models\JourneyLogLink\JourneyLogLink;
use JourneyLog\Domain\Models\JourneyLogLink\JourneyLogLinkId;
use JourneyLog\Domain\Models\JourneyLogLink\JourneyLogLinkName;
use JourneyLog\Domain\Models\Period;
use JourneyLog\Domain\Models\Story;
use JourneyLog\Domain\Models\ToOn;
use JourneyLog\Domain\Models\Url;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class ListInteractorTest extends TestCase
{
    private JourneyLogRepositoryInterface&MockInterface $repository;

    private ListInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(JourneyLogRepositoryInterface::class);

        $this->interactor = new ListInteractor($this->repository);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(ListUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function emptyJourneyLogs(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([])
            ->once();

        $response = $this->interactor->handle();

        $this->assertInstanceOf(ListOutputData::class, $response);

        $this->assertCount(0, $response->journeyLogs);
    }

    #[Test]
    public function nonEmptyJourneyLogs(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([
                new JourneyLog(
                    new JourneyLogId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                    new Story('ストーリー1'),
                    new Period(new FromOn(new ImmutableDate('2019-12-08')), new ToOn(new ImmutableDate('2019-12-08'))),
                    new OrderNo(1),
                    []
                ),
                new JourneyLog(
                    new JourneyLogId('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'),
                    new Story('ストーリー2'),
                    new Period(new FromOn(new ImmutableDate('2019-12-09')), new ToOn(new ImmutableDate('2019-12-09'))),
                    new OrderNo(2),
                    [
                        new JourneyLogLink(
                            new JourneyLogLinkId('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC'),
                            new JourneyLogLinkName('リンク'),
                            new Url('https://example.com'),
                            new OrderNo(1),
                            new JourneyLogLinkTypeId('DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD')
                        ),
                    ]
                ),
            ])
            ->once();

        $response = $this->interactor->handle();

        $this->assertInstanceOf(ListOutputData::class, $response);

        $this->assertCount(2, $response->journeyLogs);

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $response->journeyLogs[0]->journeyLogId->value);
        $this->assertSame('ストーリー1', $response->journeyLogs[0]->story->value);
        $this->assertSame('2019-12-08', $response->journeyLogs[0]->period->fromOn->value->format('Y-m-d'));
        $this->assertSame('2019-12-08', $response->journeyLogs[0]->period->toOn->value->format('Y-m-d'));
        $this->assertSame(1, $response->journeyLogs[0]->orderNo->value);
        $this->assertEmpty($response->journeyLogs[0]->journeyLogLinks);

        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $response->journeyLogs[1]->journeyLogId->value);
        $this->assertSame('ストーリー2', $response->journeyLogs[1]->story->value);
        $this->assertSame('2019-12-09', $response->journeyLogs[1]->period->fromOn->value->format('Y-m-d'));
        $this->assertSame('2019-12-09', $response->journeyLogs[1]->period->toOn->value->format('Y-m-d'));
        $this->assertSame(2, $response->journeyLogs[1]->orderNo->value);
        $this->assertCount(1, $response->journeyLogs[1]->journeyLogLinks);
        $this->assertSame('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', $response->journeyLogs[1]->journeyLogLinks[0]->journeyLogLinkId->value);
        $this->assertSame('リンク', $response->journeyLogs[1]->journeyLogLinks[0]->journeyLogLinkName->value);
        $this->assertSame('https://example.com', $response->journeyLogs[1]->journeyLogLinks[0]->url->value);
        $this->assertSame(1, $response->journeyLogs[1]->journeyLogLinks[0]->orderNo->value);
        $this->assertSame('DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', $response->journeyLogs[1]->journeyLogLinks[0]->journeyLogLinkTypeId->value);
    }
}
