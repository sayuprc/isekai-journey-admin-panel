<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLog\Application\Edit;

use JourneyLog\Application\Edit\EditInteractor;
use JourneyLog\Domain\Models\JourneyLog;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLog\UseCases\Edit\EditJourneyLogLink;
use JourneyLog\UseCases\Edit\EditRequest;
use JourneyLog\UseCases\Edit\EditUseCaseInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Application\Uuid\DummyUuidGenerator;
use Tests\TestCase;

class EditInteractorTest extends TestCase
{
    private JourneyLogRepositoryInterface&MockInterface $journeyLogRepository;

    private EditInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->journeyLogRepository = Mockery::mock(JourneyLogRepositoryInterface::class);
        $this->interactor = new EditInteractor($this->journeyLogRepository, $this->app->make(DummyUuidGenerator::class));
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(EditUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function editJourneyLogWithoutLinks(): void
    {
        $this->journeyLogRepository->shouldReceive('editJourneyLog')
            ->with(Mockery::on(
                fn ($arg) => $arg instanceof JourneyLog
                    && $arg->journeyLogId->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'
                    && $arg->story->value === 'update story'
                    && $arg->period->fromOn->value->format('Y-m-d') === '2019-12-09'
                    && $arg->period->toOn->value->format('Y-m-d') === '2019-12-09'
                    && $arg->orderNo->value === 2
                    && count($arg->journeyLogLinks) === 0
            ))
            ->once();

        $this->interactor->handle(new EditRequest(
            'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
            'update story',
            '2019-12-09',
            '2019-12-09',
            2,
            [],
        ));
    }

    #[Test]
    public function editJourneyLogWithLinks(): void
    {
        $this->journeyLogRepository->shouldReceive('editJourneyLog')
            ->with(Mockery::on(
                fn ($arg) => $arg instanceof JourneyLog
                    && $arg->journeyLogId->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'
                    && $arg->story->value === 'update story'
                    && $arg->period->fromOn->value->format('Y-m-d') === '2019-12-09'
                    && $arg->period->toOn->value->format('Y-m-d') === '2019-12-09'
                    && $arg->orderNo->value === 2
                    && count($arg->journeyLogLinks) === 2
                    && $arg->journeyLogLinks[0]->journeyLogLinkId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->journeyLogLinks[0]->journeyLogLinkName->value === 'update リンク1'
                    && $arg->journeyLogLinks[0]->url->value === 'https://example.com/1/update'
                    && $arg->journeyLogLinks[0]->orderNo->value === 2
                    && $arg->journeyLogLinks[0]->journeyLogLinkTypeId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAB'
                    && $arg->journeyLogLinks[1]->journeyLogLinkId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->journeyLogLinks[1]->journeyLogLinkName->value === 'update リンク2'
                    && $arg->journeyLogLinks[1]->url->value === 'https://example.com/2/update'
                    && $arg->journeyLogLinks[1]->orderNo->value === 3
                    && $arg->journeyLogLinks[1]->journeyLogLinkTypeId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAC'
            ))
            ->once();

        $this->interactor->handle(new EditRequest(
            'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
            'update story',
            '2019-12-09',
            '2019-12-09',
            2,
            [
                new EditJourneyLogLink(
                    'update リンク1',
                    'https://example.com/1/update',
                    2,
                    'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAB',
                ),
                new EditJourneyLogLink(
                    'update リンク2',
                    'https://example.com/2/update',
                    3,
                    'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAC',
                ),
            ],
        ));
    }
}
