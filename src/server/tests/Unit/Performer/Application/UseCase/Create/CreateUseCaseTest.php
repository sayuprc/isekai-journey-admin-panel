<?php

declare(strict_types=1);

namespace Tests\Unit\Performer\Application\UseCase\Create;

use Closure;
use Mockery;
use Mockery\MockInterface;
use Override;
use Performer\Application\UseCase\Create\CreateInputData;
use Performer\Application\UseCase\Create\CreateUseCase;
use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerRepositoryInterface;
use Performer\Domain\Services\PerformerIntegrityService;
use PHPUnit\Framework\Attributes\Test;
use ResultType\Err;
use ResultType\Ok;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\DomainValidationError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class CreateUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private MockInterface&PerformerRepositoryInterface $repository;

    private MockInterface&PerformerIntegrityService $service;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->repository = Mockery::mock(PerformerRepositoryInterface::class);
        $this->service = Mockery::mock(PerformerIntegrityService::class);
    }

    #[Test]
    public function create(): void
    {
        $performerId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $name = '共演者';
        $orderNo = 1;

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->service->shouldReceive('prepareForCreate')
            ->with($name)
            ->andReturn(new Ok($performer = $this->createPerformer($performerId, $name, $orderNo)))
            ->once();

        $this->repository->shouldReceive('save')
            ->withArgs(
                fn (Performer $arg): bool => $arg->performerId->value === $performerId
                    && $arg->name->value === $name
                    && $arg->orderNo->value === $orderNo,
            )
            ->andReturn($performer)
            ->once();

        $result = $this->getInstance()->handle(new CreateInputData($name));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function createFailsIfNameAlreadyExists(): void
    {
        $name = '共演者';

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->service->shouldReceive('prepareForCreate')
            ->with($name)
            ->andReturn(new Err(new DomainValidationError([])))
            ->once();

        $result = $this->getInstance()->handle(new CreateInputData($name));

        $this->assertTrue($result->isErr());
    }

    private function getInstance(): CreateUseCase
    {
        return new CreateUseCase(
            $this->authorizer(),
            $this->transaction,
            $this->repository,
            $this->service,
        );
    }
}
