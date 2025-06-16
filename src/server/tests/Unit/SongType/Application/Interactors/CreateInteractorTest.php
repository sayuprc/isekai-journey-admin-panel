<?php

declare(strict_types=1);

namespace Tests\Unit\SongType\Application\Interactors;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use SongType\Application\Interactors\CreateInteractor;
use SongType\Application\UseCase\Create\CreateInputData;
use SongType\Application\UseCase\Create\CreateUseCaseInterface;
use SongType\Domain\Models\SongType;
use SongType\Domain\Models\SongTypeFactoryInterface;
use SongType\Domain\Models\SongTypeId;
use SongType\Domain\Models\SongTypeName;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use SongType\Domain\Services\SongTypeNameDuplicateCheckService;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class CreateInteractorTest extends TestCase
{
    private MockInterface&SongTypeRepositoryInterface $repository;

    private MockInterface&SongTypeFactoryInterface $factory;

    private MockInterface&SongTypeNameDuplicateCheckService $service;

    private CreateInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(SongTypeRepositoryInterface::class);
        $this->factory = Mockery::mock(SongTypeFactoryInterface::class);
        $this->service = Mockery::mock(SongTypeNameDuplicateCheckService::class);

        $this->interactor = new CreateInteractor($this->repository, $this->factory, $this->service);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(CreateUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function create(): void
    {
        $this->factory->shouldReceive('create')
            ->with('楽曲種別', 1)
            ->andReturn(new SongType(
                new SongTypeId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                new SongTypeName('楽曲種別'),
                new OrderNo(1),
            ))
            ->once();

        $this->service->shouldReceive('exists')
            ->with(Mockery::on(fn (SongTypeName $arg): bool => $arg->value === '楽曲種別'))
            ->andReturnFalse()
            ->once();

        $this->repository->shouldReceive('insert')
            ->with(Mockery::on(
                fn (SongType $arg): bool => $arg->songTypeId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->songTypeName->value === '楽曲種別'
                    && $arg->orderNo->value === 1
            ))
            ->once();

        $result = $this->interactor->handle(new CreateInputData('楽曲種別', 1));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function createFailsIfNameAlreadyExists(): void
    {
        $this->factory->shouldReceive('create')
            ->with('楽曲種別', 1)
            ->andReturn(new SongType(
                new SongTypeId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                new SongTypeName('楽曲種別'),
                new OrderNo(1),
            ))
            ->once();

        $this->service->shouldReceive('exists')
            ->with(Mockery::on(fn (SongTypeName $arg): bool => $arg->value === '楽曲種別'))
            ->andReturnTrue()
            ->once();

        $result = $this->interactor->handle(new CreateInputData('楽曲種別', 1));

        $this->assertTrue($result->isErr());
    }
}
