<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLogLinkType\Application\List;

use JourneyLogLinkType\Application\List\ListInteractor;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkType;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeName;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\UseCases\List\ListOutputData;
use JourneyLogLinkType\UseCases\List\ListUseCaseInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class ListInteractorTest extends TestCase
{
    private JourneyLogLinkTypeRepositoryInterface&MockInterface $repository;

    private ListInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(JourneyLogLinkTypeRepositoryInterface::class);

        $this->interactor = new ListInteractor($this->repository);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(ListUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function emptyJourneyLogLinkTypes(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([])
            ->once();

        $response = $this->interactor->handle();

        $this->assertInstanceOf(ListOutputData::class, $response);

        $this->assertCount(0, $response->journeyLogLinkTypes);
    }

    #[Test]
    public function nonEmptyJourneyLogLinkTypes(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([
                new JourneyLogLinkType(
                    new JourneyLogLinkTypeId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                    new JourneyLogLinkTypeName('リンク'),
                    new OrderNo(1)
                ),
            ])
            ->once();

        $response = $this->interactor->handle();

        $this->assertInstanceOf(ListOutputData::class, $response);

        $this->assertCount(1, $response->journeyLogLinkTypes);

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $response->journeyLogLinkTypes[0]->journeyLogLinkTypeId->value);
        $this->assertSame('リンク', $response->journeyLogLinkTypes[0]->journeyLogLinkTypeName->value);
        $this->assertSame(1, $response->journeyLogLinkTypes[0]->orderNo->value);
    }
}
