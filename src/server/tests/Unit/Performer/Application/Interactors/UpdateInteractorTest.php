<?php

declare(strict_types=1);

namespace Tests\Unit\Performer\Application\Interactors;

use Closure;
use Mockery;
use Mockery\MockInterface;
use Performer\Application\Interactors\UpdateInteractor;
use Performer\Application\UseCase\Update\UpdateInputData;
use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerFactoryInterface;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerName;
use Performer\Domain\Models\PerformerRepositoryInterface;
use Performer\Domain\Services\PerformerNameDuplicateCheckService;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\TransactionInterface;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class UpdateInteractorTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private MockInterface&PerformerRepositoryInterface $repository;

    private MockInterface&PerformerFactoryInterface $factory;

    private MockInterface&PerformerNameDuplicateCheckService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->repository = Mockery::mock(PerformerRepositoryInterface::class);
        $this->factory = Mockery::mock(PerformerFactoryInterface::class);
        $this->service = Mockery::mock(PerformerNameDuplicateCheckService::class);
    }

    #[Test]
    public function editPerformer(): void
    {
        $this->transaction->shouldReceive('scope')
            ->with(Mockery::on(fn (Closure $_) => true))
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->factory->shouldReceive('create')
            ->with(
                Mockery::on(fn (PerformerId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                Mockery::on(fn (PerformerName $arg): bool => $arg->value === '共演者2'),
                Mockery::on(fn (OrderNo $arg): bool => $arg->value === 2),
            )
            ->andReturn($performer = $this->createPerformer('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', '共演者2', 2))
            ->once();

        $this->service->shouldReceive('existsForUpdate')
            ->with(
                Mockery::on(fn (PerformerId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                Mockery::on(fn (PerformerName $arg): bool => $arg->value === '共演者2'),
            )
            ->andReturn(false)
            ->once();

        $this->repository->shouldReceive('save')
            ->with(Mockery::on(
                fn (Performer $arg): bool => $arg->performerId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->performerName->value === '共演者2'
                    && $arg->orderNo->value === 2,
            ))
            ->andReturn($performer)
            ->once();

        $result = $this->getInstance()->handle(new UpdateInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', '共演者2', 2));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function editFailsIfNameAlreadyExists(): void
    {
        $this->transaction->shouldReceive('scope')
            ->with(Mockery::on(fn (Closure $_) => true))
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->factory->shouldReceive('create')
            ->with(
                Mockery::on(fn (PerformerId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                Mockery::on(fn (PerformerName $arg): bool => $arg->value === '共演者2'),
                Mockery::on(fn (OrderNo $arg): bool => $arg->value === 2),
            )
            ->andReturn($this->createPerformer('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', '共演者2', 2))
            ->once();

        $this->service->shouldReceive('existsForUpdate')
            ->with(
                Mockery::on(fn (PerformerId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                Mockery::on(fn (PerformerName $arg): bool => $arg->value === '共演者2'),
            )
            ->andReturn(true)
            ->once();

        $result = $this->getInstance()->handle(new UpdateInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', '共演者2', 2));

        $this->assertTrue($result->isErr());
    }

    private function getInstance(): UpdateInteractor
    {
        return new UpdateInteractor(
            $this->transaction,
            $this->repository,
            $this->factory,
            $this->service,
        );
    }
}
