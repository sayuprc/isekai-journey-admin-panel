<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLog\Application\Get;

use DateTime;
use JourneyLog\Application\Get\GetInteractor;
use JourneyLog\Domain\Models\FromOn;
use JourneyLog\Domain\Models\JourneyLog;
use JourneyLog\Domain\Models\JourneyLogId;
use JourneyLog\Domain\Models\JourneyLogLink;
use JourneyLog\Domain\Models\JourneyLogLinkId;
use JourneyLog\Domain\Models\JourneyLogLinkName;
use JourneyLog\Domain\Models\Period;
use JourneyLog\Domain\Models\Story;
use JourneyLog\Domain\Models\ToOn;
use JourneyLog\Domain\Models\Url;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLog\UseCases\Get\GetRequest;
use JourneyLog\UseCases\Get\GetResponse;
use JourneyLog\UseCases\Get\GetUseCaseInterface;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\OrderNo;
use Support\Result\Result;
use Tests\TestCase;

class GetInteractorTest extends TestCase
{
    private JourneyLogRepositoryInterface&MockInterface $journeyLogRepository;

    private GetInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->journeyLogRepository = Mockery::mock(JourneyLogRepositoryInterface::class);
        $this->interactor = new GetInteractor($this->journeyLogRepository);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(GetUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function getJourneyLogWithoutLinks(): void
    {
        $this->journeyLogRepository->shouldReceive('find')
            ->with(Mockery::on(
                fn ($arg) => $arg instanceof JourneyLogId
                    && $arg->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'
            ))
            ->andReturnUsing(
                fn () => new JourneyLog(
                    new JourneyLogId('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'),
                    new Story('ストーリー'),
                    new Period(new FromOn(new DateTime('2019-12-08')), new ToOn(new DateTime('2019-12-08'))),
                    new OrderNo(1),
                    []
                )
            )
            ->once();

        $result = $this->interactor->handle(new GetRequest('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));

        $this->assertInstanceOf(Result::class, $result);

        $this->assertTrue($result->isOk());

        $response = $result->getValue();

        $this->assertInstanceOf(GetResponse::class, $response);

        $this->assertInstanceOf(JourneyLog::class, $response->journeyLog);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $response->journeyLog->journeyLogId->value);
        $this->assertSame('ストーリー', $response->journeyLog->story->value);
        $this->assertSame('2019-12-08', $response->journeyLog->period->fromOn->value->format('Y-m-d'));
        $this->assertSame('2019-12-08', $response->journeyLog->period->toOn->value->format('Y-m-d'));
        $this->assertSame(1, $response->journeyLog->orderNo->value);
        $this->assertEmpty($response->journeyLog->journeyLogLinks);
    }

    #[Test]
    public function getJourneyLogWithLinks(): void
    {
        $this->journeyLogRepository->shouldReceive('find')
            ->with(Mockery::on(
                fn ($arg) => $arg instanceof JourneyLogId
                    && $arg->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'
            ))
            ->andReturnUsing(
                fn () => new JourneyLog(
                    new JourneyLogId('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'),
                    new Story('ストーリー'),
                    new Period(new FromOn(new DateTime('2019-12-08')), new ToOn(new DateTime('2019-12-08'))),
                    new OrderNo(1),
                    [
                        new JourneyLogLink(
                            new JourneyLogLinkId('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC'),
                            new JourneyLogLinkName('リンク'),
                            new Url('https://example.com'),
                            new OrderNo(1),
                            new JourneyLogLinkTypeId('DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD')
                        ),
                    ]
                )
            )
            ->once();

        $result = $this->interactor->handle(new GetRequest('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isOk());

        $response = $result->getValue();

        $this->assertInstanceOf(GetResponse::class, $response);

        $this->assertInstanceOf(JourneyLog::class, $response->journeyLog);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $response->journeyLog->journeyLogId->value);
        $this->assertSame('ストーリー', $response->journeyLog->story->value);
        $this->assertSame('2019-12-08', $response->journeyLog->period->fromOn->value->format('Y-m-d'));
        $this->assertSame('2019-12-08', $response->journeyLog->period->toOn->value->format('Y-m-d'));
        $this->assertSame(1, $response->journeyLog->orderNo->value);
        $this->assertCount(1, $response->journeyLog->journeyLogLinks);
        $this->assertSame('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', $response->journeyLog->journeyLogLinks[0]->journeyLogLinkId->value);
        $this->assertSame('リンク', $response->journeyLog->journeyLogLinks[0]->journeyLogLinkName->value);
        $this->assertSame('https://example.com', $response->journeyLog->journeyLogLinks[0]->url->value);
        $this->assertSame(1, $response->journeyLog->journeyLogLinks[0]->orderNo->value);
        $this->assertSame('DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', $response->journeyLog->journeyLogLinks[0]->journeyLogLinkTypeId->value);
    }

    #[Test]
    public function failureGetJourneyLog(): void
    {
        $this->journeyLogRepository->shouldReceive('find')
            ->with(Mockery::on(
                fn ($arg) => $arg instanceof JourneyLogId
                    && $arg->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'
            ))
            ->andReturnNull()
            ->once();

        $result = $this->interactor->handle(new GetRequest('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));

        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->isOk());

        $this->assertSame('Journey log not found: BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $result->getErr());
    }
}
