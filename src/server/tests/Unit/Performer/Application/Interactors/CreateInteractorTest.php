<?php

declare(strict_types=1);

namespace Tests\Unit\Performer\Application\Interactors;

use Closure;
use Mockery;
use Mockery\MockInterface;
use Performer\Application\Interactors\CreateInteractor;
use Performer\Application\UseCase\Create\CreateInputData;
use Performer\Application\UseCase\Create\CreateUseCaseInterface;
use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerFactoryInterface;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerName;
use Performer\Domain\Models\PerformerRepositoryInterface;
use Performer\Domain\Services\PerformerNameDuplicateCheckService;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\TransactionInterface;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class CreateInteractorTest extends TestCase
{
    private MockInterface&TransactionInterface $transaction;

    private MockInterface&PerformerRepositoryInterface $repository;

    private MockInterface&PerformerFactoryInterface $factory;

    private MockInterface&PerformerNameDuplicateCheckService $service;

    private CreateInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->repository = Mockery::mock(PerformerRepositoryInterface::class);
        $this->factory = Mockery::mock(PerformerFactoryInterface::class);
        $this->service = Mockery::mock(PerformerNameDuplicateCheckService::class);

        $this->interactor = new CreateInteractor($this->transaction, $this->repository, $this->factory, $this->service);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(CreateUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function create(): void
    {
        $this->transaction->shouldReceive('scope')
            ->with(Mockery::on(fn (Closure $_) => true))
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->factory->shouldReceive('create')
            ->with('共演者', 1)
            ->andReturn($performer = new Performer(
                new PerformerId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                new PerformerName('共演者'),
                new OrderNo(1),
            ))
            ->once();

        $this->service->shouldReceive('exists')
            ->with(Mockery::on(fn (PerformerName $arg): bool => $arg->value === '共演者'))
            ->andreturn(false)
            ->once();

        $this->repository->shouldReceive('save')
            ->with(Mockery::on(
                fn (Performer $arg): bool => $arg->performerId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->performerName->value === '共演者'
            ))
            ->andReturn($performer)
            ->once();

        $result = $this->interactor->handle(new CreateInputData('共演者', 1));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function createFailsIfNameAlreadyExists(): void
    {
        $this->transaction->shouldReceive('scope')
            ->with(Mockery::on(fn (Closure $_) => true))
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->factory->shouldReceive('create')
            ->with('共演者', 1)
            ->andReturn(new Performer(
                new PerformerId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                new PerformerName('共演者'),
                new OrderNo(1),
            ))
            ->once();

        $this->service->shouldReceive('exists')
            ->with(Mockery::on(fn (PerformerName $arg): bool => $arg->value === '共演者'))
            ->andreturn(true)
            ->once();

        $result = $this->interactor->handle(new CreateInputData('共演者', 1));

        $this->assertTrue($result->isErr());
    }
}
