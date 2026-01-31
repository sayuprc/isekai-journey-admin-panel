<?php

declare(strict_types=1);

namespace Tests\Unit\Performer\Application\Interactors;

use Closure;
use Mockery;
use Mockery\MockInterface;
use Performer\Application\Interactors\CreateInteractor;
use Performer\Application\UseCase\Create\CreateInputData;
use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerFactoryInterface;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerName;
use Performer\Domain\Models\PerformerRepositoryInterface;
use Performer\Domain\Services\PerformerNameDuplicateCheckService;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\TransactionInterface;
use Support\Contracts\UuidGeneratorInterface;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class CreateInteractorTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private MockInterface&PerformerRepositoryInterface $repository;

    private MockInterface&PerformerFactoryInterface $factory;

    private MockInterface&PerformerNameDuplicateCheckService $service;

    private MockInterface&UuidGeneratorInterface $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->repository = Mockery::mock(PerformerRepositoryInterface::class);
        $this->factory = Mockery::mock(PerformerFactoryInterface::class);
        $this->service = Mockery::mock(PerformerNameDuplicateCheckService::class);
        $this->generator = Mockery::mock(UuidGeneratorInterface::class);
    }

    #[Test]
    public function create(): void
    {
        $this->generator->shouldReceive('generate')
            ->andReturn('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->once();

        $this->transaction->shouldReceive('scope')
            ->with(Mockery::on(fn (Closure $_) => true))
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->factory->shouldReceive('create')
            ->with(
                Mockery::on(fn (PerformerId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                Mockery::on(fn (PerformerName $arg): bool => $arg->value === '共演者'),
                Mockery::on(fn (OrderNo $arg): bool => $arg->value === 1),
            )
            ->andReturn($performer = $this->createPerformer('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', '共演者', 1))
            ->once();

        $this->service->shouldReceive('exists')
            ->with(Mockery::on(fn (PerformerName $arg): bool => $arg->value === '共演者'))
            ->andreturn(false)
            ->once();

        $this->repository->shouldReceive('save')
            ->with(
                Mockery::on(
                    fn (Performer $arg): bool => $arg->performerId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                        && $arg->performerName->value === '共演者',
                ),
            )
            ->andReturn($performer)
            ->once();

        $result = $this->getInstance()->handle(new CreateInputData('共演者', 1));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function createFailsIfNameAlreadyExists(): void
    {
        $this->generator->shouldReceive('generate')
            ->andReturn('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->once();

        $this->transaction->shouldReceive('scope')
            ->with(Mockery::on(fn (Closure $_) => true))
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->factory->shouldReceive('create')
            ->with(
                Mockery::on(fn (PerformerId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                Mockery::on(fn (PerformerName $arg): bool => $arg->value === '共演者'),
                Mockery::on(fn (OrderNo $arg): bool => $arg->value === 1),
            )
            ->andReturn($this->createPerformer('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', '共演者', 1))
            ->once();

        $this->service->shouldReceive('exists')
            ->with(Mockery::on(fn (PerformerName $arg): bool => $arg->value === '共演者'))
            ->andreturn(true)
            ->once();

        $result = $this->getInstance()->handle(new CreateInputData('共演者', 1));

        $this->assertTrue($result->isErr());
    }

    private function getInstance(): CreateInteractor
    {
        return new CreateInteractor(
            $this->transaction,
            $this->repository,
            $this->factory,
            $this->service,
            $this->generator,
        );
    }
}
