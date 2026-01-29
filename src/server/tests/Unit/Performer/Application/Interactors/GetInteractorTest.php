<?php

declare(strict_types=1);

namespace Tests\Unit\Performer\Application\Interactors;

use Mockery;
use Mockery\MockInterface;
use Performer\Application\Interactors\GetInteractor;
use Performer\Application\UseCase\Get\GetInputData;
use Performer\Application\UseCase\Get\GetOutputData;
use Performer\Application\UseCase\Get\GetUseCaseInterface;
use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use ResultType\Result;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class GetInteractorTest extends TestCase
{
    use EntityFactory;

    private MockInterface&PerformerRepositoryInterface $repository;

    private GetInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(PerformerRepositoryInterface::class);

        $this->interactor = new GetInteractor($this->repository);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(GetUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function getPerformer(): void
    {
        $this->repository->shouldReceive('find')
            ->with(Mockery::on(fn (PerformerId $arg): bool => $arg->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'))
            ->andReturn($this->createPerformer('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', '共演者名', 1))
            ->once();

        $result = $this->interactor->handle(new GetInputData('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertInstanceOf(GetOutputData::class, $response);

        $this->assertInstanceOf(Performer::class, $response->performer);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $response->performer->performerId->value);
        $this->assertSame('共演者名', $response->performer->performerName->value);
        $this->assertSame(1, $response->performer->orderNo->value);
    }

    #[Test]
    public function failureGetPerformer(): void
    {
        $this->repository->shouldReceive('find')
            ->with(Mockery::on(fn (PerformerId $arg): bool => $arg->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'))
            ->andReturnNull()
            ->once();

        $result = $this->interactor->handle(new GetInputData('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));

        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->isOk());

        $this->assertSame('Performer not found: BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $result->unwrapErr());
    }
}
