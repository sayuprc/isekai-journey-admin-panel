<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Application\Interactors;

use Override;
use Closure;
use Creator\Application\Interactors\UpdateInteractor;
use Creator\Application\UseCase\Update\UpdateInputData;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Domain\Services\CreatorIntegrityService;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use ResultType\Err;
use ResultType\Ok;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\DomainValidationError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class UpdateInteractorTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private CreatorRepositoryInterface&MockInterface $repository;

    private CreatorIntegrityService&MockInterface $service;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->repository = Mockery::mock(CreatorRepositoryInterface::class);
        $this->service = Mockery::mock(CreatorIntegrityService::class);
    }

    #[Test]
    public function editCreator(): void
    {
        $creatorId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $name = 'クリエイター';
        $orderNo = 1;

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->service->shouldReceive('prepareForUpdate')
            ->with($creatorId, $name, $orderNo)
            ->andReturn(new Ok($creator = $this->createCreator($creatorId, $name, $orderNo)))
            ->once();

        $this->repository->shouldReceive('save')
            ->withArgs(
                fn (Creator $arg): bool => $arg->creatorId->value === $creatorId
                    && $arg->name->value === $name,
            )
            ->andReturn($creator)
            ->once();

        $result = $this->getInstance()->handle(new UpdateInputData($creatorId, $name, $orderNo));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function editFailsIfNameAlreadyExists(): void
    {
        $creatorId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $name = 'クリエイター';
        $orderNo = 1;

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->service->shouldReceive('prepareForUpdate')
            ->with($creatorId, $name, $orderNo)
            ->andReturn(new Err(new DomainValidationError([])))
            ->once();

        $result = $this->getInstance()->handle(new UpdateInputData($creatorId, $name, $orderNo));

        $this->assertTrue($result->isErr());
    }

    private function getInstance(): UpdateInteractor
    {
        return new UpdateInteractor(
            $this->privilegedContext(),
            $this->transaction,
            $this->repository,
            $this->service,
        );
    }
}
