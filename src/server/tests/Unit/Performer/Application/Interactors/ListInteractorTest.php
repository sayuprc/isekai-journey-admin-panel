<?php

declare(strict_types=1);

namespace Tests\Unit\Performer\Application\Interactors;

use Mockery;
use Mockery\MockInterface;
use Performer\Application\Interactors\ListInteractor;
use Performer\Application\UseCase\List\ListOutputData;
use Performer\Application\UseCase\List\ListUseCaseInterface;
use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerName;
use Performer\Domain\Models\PerformerRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class ListInteractorTest extends TestCase
{
    private MockInterface&PerformerRepositoryInterface $repository;

    private ListInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(PerformerRepositoryInterface::class);

        $this->interactor = new ListInteractor($this->repository);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(ListUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function emptyPerformers(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([])
            ->once();

        $response = $this->interactor->handle();

        $this->assertInstanceOf(ListOutputData::class, $response);

        $this->assertCount(0, $response->performers);
    }

    #[Test]
    public function nonEmptyPerformers(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([
                new Performer(
                    new PerformerId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                    new PerformerName('パフォーマーA'),
                    new OrderNo(1),
                ),
                new Performer(
                    new PerformerId('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'),
                    new PerformerName('パフォーマーB'),
                    new OrderNo(2),
                ),
            ])
            ->once();

        $response = $this->interactor->handle();

        $this->assertInstanceOf(ListOutputData::class, $response);

        $this->assertCount(2, $response->performers);

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $response->performers[0]->performerId->value);
        $this->assertSame('パフォーマーA', $response->performers[0]->performerName->value);
        $this->assertSame(1, $response->performers[0]->orderNo->value);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $response->performers[1]->performerId->value);
        $this->assertSame('パフォーマーB', $response->performers[1]->performerName->value);
        $this->assertSame(2, $response->performers[1]->orderNo->value);
    }
}
