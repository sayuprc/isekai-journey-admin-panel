<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLog\Application\Edit;

use DateTimeImmutable;
use DateTimeInterface;
use JourneyLog\Application\Edit\EditInteractor;
use JourneyLog\Domain\Dtos\CreateJourneyLogLinkData;
use JourneyLog\Domain\Models\FromOn;
use JourneyLog\Domain\Models\JourneyLog;
use JourneyLog\Domain\Models\JourneyLogFactoryInterface;
use JourneyLog\Domain\Models\JourneyLogId;
use JourneyLog\Domain\Models\JourneyLogLink\JourneyLogLink;
use JourneyLog\Domain\Models\JourneyLogLink\JourneyLogLinkId;
use JourneyLog\Domain\Models\JourneyLogLink\JourneyLogLinkName;
use JourneyLog\Domain\Models\Period;
use JourneyLog\Domain\Models\Story;
use JourneyLog\Domain\Models\ToOn;
use JourneyLog\Domain\Models\Url;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLog\UseCases\Edit\EditRequest;
use JourneyLog\UseCases\Edit\EditUseCaseInterface;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class EditInteractorTest extends TestCase
{
    private JourneyLogRepositoryInterface&MockInterface $journeyLogRepository;

    private JourneyLogFactoryInterface&MockInterface $factory;

    private EditInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->journeyLogRepository = Mockery::mock(JourneyLogRepositoryInterface::class);
        $this->factory = Mockery::mock(JourneyLogFactoryInterface::class);
        $this->interactor = new EditInteractor($this->journeyLogRepository, $this->factory);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(EditUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function editJourneyLogWithoutLinks(): void
    {
        $this->factory->shouldReceive('createForUpdate')
            ->with(
                'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
                'story',
                Mockery::on(fn (DateTimeInterface $arg): bool => $arg->format('Y-m-d') === '2019-12-08'),
                Mockery::on(fn (DateTimeInterface $arg): bool => $arg->format('Y-m-d') === '2019-12-09'),
                1,
                [],
            )
            ->andReturnUsing(fn (): JourneyLog => new JourneyLog(
                new JourneyLogId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                new Story('story'),
                new Period(new FromOn(new DateTimeImmutable('2019-12-08')), new ToOn(new DateTimeImmutable('2019-12-09'))),
                new OrderNo(1),
                []
            ))
            ->once();

        $this->journeyLogRepository->shouldReceive('update')
            ->with(Mockery::on(
                fn (JourneyLog $arg): bool => $arg->journeyLogId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->story->value === 'story'
                    && $arg->period->fromOn->value->format('Y-m-d') === '2019-12-08'
                    && $arg->period->toOn->value->format('Y-m-d') === '2019-12-09'
                    && $arg->orderNo->value === 1
                    && count($arg->journeyLogLinks) === 0
            ))
            ->once();

        $this->interactor->handle(new EditRequest(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            'story',
            new DateTimeImmutable('2019-12-08'),
            new DateTimeImmutable('2019-12-09'),
            1,
            [],
        ));
    }

    #[Test]
    public function editJourneyLogWithLinks(): void
    {
        $this->factory->shouldReceive('createForUpdate')
            ->with(
                'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
                'story',
                Mockery::on(fn (DateTimeInterface $arg): bool => $arg->format('Y-m-d') === '2019-12-08'),
                Mockery::on(fn (DateTimeInterface $arg): bool => $arg->format('Y-m-d') === '2019-12-09'),
                1,
                Mockery::on(
                    fn (array $args): bool => count($args) === 2
                        && $args[0] instanceof CreateJourneyLogLinkData
                        && $args[0]->journeyLogLinkName === 'リンク1'
                        && $args[0]->url === 'https://example.com/1'
                        && $args[0]->orderNo === 1
                        && $args[0]->journeyLogLinkTypeId === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'
                        && $args[1] instanceof CreateJourneyLogLinkData
                        && $args[1]->journeyLogLinkName === 'リンク2'
                        && $args[1]->url === 'https://example.com/2'
                        && $args[1]->orderNo === 2
                        && $args[1]->journeyLogLinkTypeId === 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC'
                ),
            )
            ->andReturnUsing(fn (): JourneyLog => new JourneyLog(
                new JourneyLogId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                new Story('story'),
                new Period(new FromOn(new DateTimeImmutable('2019-12-08')), new ToOn(new DateTimeImmutable('2019-12-09'))),
                new OrderNo(1),
                [
                    new JourneyLogLink(
                        new JourneyLogLinkId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                        new JourneyLogLinkName('リンク1'),
                        new Url('https://example.com/1'),
                        new OrderNo(1),
                        new JourneyLogLinkTypeId('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB')
                    ),
                    new JourneyLogLink(
                        new JourneyLogLinkId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                        new JourneyLogLinkName('リンク2'),
                        new Url('https://example.com/2'),
                        new OrderNo(2),
                        new JourneyLogLinkTypeId('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC')
                    ),
                ],
            ))
            ->once();

        $this->journeyLogRepository->shouldReceive('update')
            ->with(Mockery::on(
                fn (JourneyLog $arg): bool => $arg->journeyLogId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->story->value === 'story'
                    && $arg->period->fromOn->value->format('Y-m-d') === '2019-12-08'
                    && $arg->period->toOn->value->format('Y-m-d') === '2019-12-09'
                    && $arg->orderNo->value === 1
                    && count($arg->journeyLogLinks) === 2
                    && $arg->journeyLogLinks[0]->journeyLogLinkId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->journeyLogLinks[0]->journeyLogLinkName->value === 'リンク1'
                    && $arg->journeyLogLinks[0]->url->value === 'https://example.com/1'
                    && $arg->journeyLogLinks[0]->orderNo->value === 1
                    && $arg->journeyLogLinks[0]->journeyLogLinkTypeId->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'
                    && $arg->journeyLogLinks[1]->journeyLogLinkId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->journeyLogLinks[1]->journeyLogLinkName->value === 'リンク2'
                    && $arg->journeyLogLinks[1]->url->value === 'https://example.com/2'
                    && $arg->journeyLogLinks[1]->orderNo->value === 2
                    && $arg->journeyLogLinks[1]->journeyLogLinkTypeId->value === 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC'
            ))
            ->once();

        $this->interactor->handle(new EditRequest(
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
            ],
        ));
    }
}
