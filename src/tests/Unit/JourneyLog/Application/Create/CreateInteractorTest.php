<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLog\Application\Create;

use JourneyLog\Application\Create\CreateInteractor;
use JourneyLog\Domain\Models\JourneyLog;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLog\UseCases\Create\CreateJourneyLogLink;
use JourneyLog\UseCases\Create\CreateRequest;
use JourneyLog\UseCases\Create\CreateUseCaseInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Application\Uuid\DummyUuidGenerator;
use Tests\TestCase;

class CreateInteractorTest extends TestCase
{
    private JourneyLogRepositoryInterface&MockInterface $journeyLogRepository;

    private CreateInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->journeyLogRepository = Mockery::mock(JourneyLogRepositoryInterface::class);
        $this->interactor = new CreateInteractor($this->journeyLogRepository, $this->app->make(DummyUuidGenerator::class));
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(CreateUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function createWithoutLinks(): void
    {
        $this->journeyLogRepository->shouldReceive('insert')
            ->with(Mockery::on(
                fn ($arg) => $arg instanceof JourneyLog
                    && $arg->journeyLogId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->story->value === 'story'
                    && $arg->period->fromOn->value->format('Y-m-d') === '2019-12-08'
                    && $arg->period->toOn->value->format('Y-m-d') === '2019-12-08'
                    && $arg->orderNo->value === 1
                    && count($arg->journeyLogLinks) === 0
            ))
            ->once();

        $this->interactor->handle(new CreateRequest(
            'story',
            '2019-12-08',
            '2019-12-08',
            1,
            [],
        ));
    }

    #[Test]
    public function createWithLinks(): void
    {
        $this->journeyLogRepository->shouldReceive('insert')
            ->with(Mockery::on(
                fn ($arg) => $arg instanceof JourneyLog
                    && $arg->journeyLogId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->story->value === 'story'
                    && $arg->period->fromOn->value->format('Y-m-d') === '2019-12-08'
                    && $arg->period->toOn->value->format('Y-m-d') === '2019-12-08'
                    && $arg->orderNo->value === 1
                    && count($arg->journeyLogLinks) === 2
                    && $arg->journeyLogLinks[0]->journeyLogLinkId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->journeyLogLinks[0]->journeyLogLinkName->value === 'リンク1'
                    && $arg->journeyLogLinks[0]->url->value === 'https://example.com/1'
                    && $arg->journeyLogLinks[0]->orderNo->value === 1
                    && $arg->journeyLogLinks[0]->journeyLogLinkTypeId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAB'
                    && $arg->journeyLogLinks[1]->journeyLogLinkId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->journeyLogLinks[1]->journeyLogLinkName->value === 'リンク2'
                    && $arg->journeyLogLinks[1]->url->value === 'https://example.com/2'
                    && $arg->journeyLogLinks[1]->orderNo->value === 2
                    && $arg->journeyLogLinks[1]->journeyLogLinkTypeId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAC'
            ))
            ->once();

        $this->interactor->handle(new CreateRequest(
            'story',
            '2019-12-08',
            '2019-12-08',
            1,
            [
                new CreateJourneyLogLink(
                    'リンク1',
                    'https://example.com/1',
                    1,
                    'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAB',
                ),
                new CreateJourneyLogLink(
                    'リンク2',
                    'https://example.com/2',
                    2,
                    'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAC',
                ),
            ],
        ));
    }
}
