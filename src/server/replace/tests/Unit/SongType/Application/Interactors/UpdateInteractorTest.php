<?php

declare(strict_types=1);

namespace Tests\Unit\SongType\Application\Interactors;

use Closure;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use SongType\Application\Interactors\UpdateInteractor;
use SongType\Application\UseCase\Update\UpdateInputData;
use SongType\Application\UseCase\Update\UpdateUseCaseInterface;
use SongType\Domain\Models\SongType;
use SongType\Domain\Models\SongTypeFactoryInterface;
use SongType\Domain\Models\SongTypeId;
use SongType\Domain\Models\SongTypeName;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use SongType\Domain\Services\SongTypeNameDuplicateCheckService;
use Support\Contracts\TransactionInterface;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class UpdateInteractorTest extends TestCase
{
    private MockInterface&TransactionInterface $transaction;

    private MockInterface&SongTypeRepositoryInterface $repository;

    private MockInterface&SongTypeFactoryInterface $factory;

    private MockInterface&SongTypeNameDuplicateCheckService $service;

    private UpdateInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->repository = Mockery::mock(SongTypeRepositoryInterface::class);
        $this->factory = Mockery::mock(SongTypeFactoryInterface::class);
        $this->service = Mockery::mock(SongTypeNameDuplicateCheckService::class);

        $this->interactor = new UpdateInteractor($this->transaction, $this->repository, $this->factory, $this->service);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(UpdateUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function editCreator(): void
    {
        $this->transaction->shouldReceive('scope')
            ->with(Mockery::on(fn (Closure $_) => true))
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->factory->shouldReceive('reconstitute')
            ->with('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', '楽曲種別', 1)
            ->andReturn(new SongType(
                new SongTypeId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                new SongTypeName('楽曲種別'),
                new OrderNo(1),
            ))
            ->once();

        $this->service->shouldReceive('existsForUpdate')
            ->with(
                Mockery::on(fn (SongTypeId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                Mockery::on(fn (SongTypeName $arg): bool => $arg->value === '楽曲種別'),
            )
            ->andReturnFalse()
            ->once();

        $this->repository->shouldReceive('update')
            ->with(Mockery::on(
                fn (SongType $arg): bool => $arg->songTypeId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->songTypeName->value === '楽曲種別'
                    && $arg->orderNo->value === 1
            ))
            ->andReturn(new SongTypeId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'))
            ->once();

        $result = $this->interactor->handle(new UpdateInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', '楽曲種別', 1));

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
            ->with('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', '楽曲種別', 1)
            ->andReturn(new SongType(
                new SongTypeId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                new SongTypeName('楽曲種別'),
                new OrderNo(1),
            ))
            ->once();

        $this->service->shouldReceive('existsForUpdate')
            ->with(
                Mockery::on(fn (SongTypeId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                Mockery::on(fn (SongTypeName $arg): bool => $arg->value === '楽曲種別')
            )
            ->andReturnTrue()
            ->once();

        $result = $this->interactor->handle(new UpdateInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', '楽曲種別', 1));

        $this->assertTrue($result->isErr());
    }
}
