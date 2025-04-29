<?php

declare(strict_types=1);

namespace Tests\Unit\SongType\Application\Get;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use SongType\Application\Get\GetInteractor;
use SongType\Domain\Models\SongType;
use SongType\Domain\Models\SongTypeId;
use SongType\Domain\Models\SongTypeName;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use SongType\UseCases\Get\GetInputData;
use SongType\UseCases\Get\GetOutputData;
use SongType\UseCases\Get\GetUseCaseInterface;
use Support\Domain\ValueObjects\OrderNo;
use Support\ResultType\Result;
use Tests\TestCase;

class GetInteractorTest extends TestCase
{
    private MockInterface&SongTypeRepositoryInterface $repository;

    private GetInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(SongTypeRepositoryInterface::class);

        $this->interactor = new GetInteractor($this->repository);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(GetUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function getSongType(): void
    {
        $this->repository->shouldReceive('find')
            ->with(Mockery::on(fn (SongTypeId $arg): bool => $arg->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'))
            ->andReturn(new SongType(
                new SongTypeId('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'),
                new SongTypeName('楽曲種別'),
                new OrderNo(1),
            ))
            ->once();

        $result = $this->interactor->handle(new GetInputData('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertInstanceOf(GetOutputData::class, $response);

        $this->assertInstanceOf(SongType::class, $response->songType);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $response->songType->songTypeId->value);
        $this->assertSame('楽曲種別', $response->songType->songTypeName->value);
        $this->assertSame(1, $response->songType->orderNo->value);
    }

    #[Test]
    public function failureGetSongType(): void
    {
        $this->repository->shouldReceive('find')
            ->with(Mockery::on(fn (SongTypeId $arg): bool => $arg->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'))
            ->andReturnNull()
            ->once();

        $result = $this->interactor->handle(new GetInputData('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));

        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->isOk());

        $this->assertSame('Song type not found: BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $result->unwrapErr());
    }
}
