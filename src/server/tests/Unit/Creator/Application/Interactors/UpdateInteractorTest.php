<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Application\Interactors;

use Closure;
use Creator\Application\Interactors\UpdateInteractor;
use Creator\Application\UseCase\Update\UpdateInputData;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Domain\Services\CreatorNameDuplicateCheckService;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\TransactionInterface;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class UpdateInteractorTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private CreatorRepositoryInterface&MockInterface $repository;

    private CreatorFactoryInterface&MockInterface $factory;

    private CreatorNameDuplicateCheckService&MockInterface $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->repository = Mockery::mock(CreatorRepositoryInterface::class);
        $this->factory = Mockery::mock(CreatorFactoryInterface::class);
        $this->service = Mockery::mock(CreatorNameDuplicateCheckService::class);
    }

    #[Test]
    public function editCreator(): void
    {
        $this->transaction->shouldReceive('scope')
            ->with(Mockery::on(fn (Closure $_) => true))
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->factory->shouldReceive('create')
            ->with(
                Mockery::on(fn (CreatorId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                Mockery::on(fn (CreatorName $arg): bool => $arg->value === 'クリエイター'),
            )
            ->andReturn($creator = $this->createCreator('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'クリエイター'))
            ->once();

        $this->service->shouldReceive('exists')
            ->with(Mockery::on(fn (CreatorName $arg): bool => $arg->value === 'クリエイター'))
            ->andReturn(false)
            ->once();

        $this->repository->shouldReceive('save')
            ->with(
                Mockery::on(
                    fn (Creator $arg): bool => $arg->creatorId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                        && $arg->creatorName->value === 'クリエイター',
                ),
            )
            ->andReturn($creator)
            ->once();

        $result = $this->getInstance()->handle(new UpdateInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'クリエイター'));

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
                Mockery::on(fn (CreatorId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                Mockery::on(fn (CreatorName $arg): bool => $arg->value === 'クリエイター'),
            )
            ->andReturn($this->createCreator('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'クリエイター'))
            ->once();

        $this->service->shouldReceive('exists')
            ->with(Mockery::on(fn (CreatorName $arg): bool => $arg->value === 'クリエイター'))
            ->andReturn(true)
            ->once();

        $result = $this->getInstance()->handle(new UpdateInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'クリエイター'));

        $this->assertTrue($result->isErr());
    }

    private function getInstance(): UpdateInteractor
    {
        return new UpdateInteractor($this->transaction, $this->repository, $this->factory, $this->service);
    }
}
