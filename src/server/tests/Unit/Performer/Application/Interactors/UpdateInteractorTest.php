<?php

declare(strict_types=1);

namespace Tests\Unit\Performer\Application\Interactors;

use Closure;
use Mockery;
use Mockery\MockInterface;
use Performer\Application\Interactors\UpdateInteractor;
use Performer\Application\UseCase\Update\UpdateInputData;
use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerRepositoryInterface;
use Performer\Domain\Services\PerformerIntegrityService;
use PHPUnit\Framework\Attributes\Test;
use ResultType\Err;
use ResultType\Ok;
use Support\Contracts\TransactionInterface;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class UpdateInteractorTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private MockInterface&PerformerRepositoryInterface $repository;

    private MockInterface&PerformerIntegrityService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->repository = Mockery::mock(PerformerRepositoryInterface::class);
        $this->service = Mockery::mock(PerformerIntegrityService::class);
    }

    #[Test]
    public function editPerformer(): void
    {
        $performerId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $performerName = '共演者';
        $orderNo = 1;

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->service->shouldReceive('prepareForUpdate')
            ->with($performerId, $performerName, $orderNo)
            ->andReturn(new Ok($performer = $this->createPerformer($performerId, $performerName, $orderNo)))
            ->once();

        $this->repository->shouldReceive('save')
            ->withArgs(
                fn (Performer $arg): bool => $arg->performerId->value === $performerId
                    && $arg->performerName->value === $performerName
                    && $arg->orderNo->value === $orderNo,
            )
            ->andReturn($performer)
            ->once();

        $result = $this->getInstance()->handle(new UpdateInputData($performerId, $performerName, $orderNo));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function editFailsIfNameAlreadyExists(): void
    {
        $performerId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $performerName = '共演者';
        $orderNo = 1;

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->service->shouldReceive('prepareForUpdate')
            ->with($performerId, $performerName, $orderNo)
            ->andReturn(new Err(''))
            ->once();

        $result = $this->getInstance()->handle(new UpdateInputData($performerId, $performerName, $orderNo));

        $this->assertTrue($result->isErr());
    }

    private function getInstance(): UpdateInteractor
    {
        return new UpdateInteractor(
            $this->transaction,
            $this->repository,
            $this->service,
        );
    }
}
