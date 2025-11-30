<?php

declare(strict_types=1);

namespace Tests\Unit\Performer\Application\Interactors;

use Closure;
use Mockery;
use Mockery\MockInterface;
use Performer\Application\Interactors\UpdateInteractor;
use Performer\Application\UseCase\Update\UpdateInputData;
use Performer\Application\UseCase\Update\UpdateUseCaseInterface;
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

class UpdateInteractorTest extends TestCase
{
    private MockInterface&TransactionInterface $transaction;

    private MockInterface&PerformerRepositoryInterface $repository;

    private MockInterface&PerformerFactoryInterface $factory;

    private MockInterface&PerformerNameDuplicateCheckService $service;

    private UpdateInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->repository = Mockery::mock(PerformerRepositoryInterface::class);
        $this->factory = Mockery::mock(PerformerFactoryInterface::class);
        $this->service = Mockery::mock(PerformerNameDuplicateCheckService::class);

        $this->interactor = new UpdateInteractor($this->transaction, $this->repository, $this->factory, $this->service);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(UpdateUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function editPerformer(): void
    {
        $this->transaction->shouldReceive('scope')
            ->with(Mockery::on(fn (Closure $_) => true))
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->factory->shouldReceive('reconstitute')
            ->with('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', '共演者2', 2)
            ->andReturn(new Performer(
                new PerformerId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                new PerformerName('共演者2'),
                new OrderNo(2)
            ))
            ->once();

        $this->service->shouldReceive('existsForUpdate')
            ->with(
                Mockery::on(fn (PerformerId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                Mockery::on(fn (PerformerName $arg): bool => $arg->value === '共演者2')
            )
            ->andReturn(false)
            ->once();

        $this->repository->shouldReceive('update')
            ->with(Mockery::on(
                fn (Performer $arg): bool => $arg->performerId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->performerName->value === '共演者2'
                    && $arg->orderNo->value === 2
            ))
            ->andReturn(new PerformerId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'))
            ->once();

        $result = $this->interactor->handle(new UpdateInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', '共演者2', 2));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function editFailsIfNameAlreadyExists(): void
    {
        $this->transaction->shouldReceive('scope')
            ->with(Mockery::on(fn (Closure $_) => true))
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->factory->shouldReceive('reconstitute')
            ->with('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', '共演者2', 2)
            ->andReturn(new Performer(
                new PerformerId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                new PerformerName('共演者2'),
                new OrderNo(2)
            ))
            ->once();

        $this->service->shouldReceive('existsForUpdate')
            ->with(
                Mockery::on(fn (PerformerId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                Mockery::on(fn (PerformerName $arg): bool => $arg->value === '共演者2'),
            )
            ->andReturn(true)
            ->once();

        $result = $this->interactor->handle(new UpdateInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', '共演者2', 2));

        $this->assertTrue($result->isErr());
    }
}
