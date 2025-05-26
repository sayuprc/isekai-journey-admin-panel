<?php

declare(strict_types=1);

namespace Tests\Unit\SongType\Application\List;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use SongType\Application\List\ListInteractor;
use SongType\Domain\Models\SongType;
use SongType\Domain\Models\SongTypeId;
use SongType\Domain\Models\SongTypeName;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use SongType\UseCases\List\ListOutputData;
use SongType\UseCases\List\ListUseCaseInterface;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class ListInteractorTest extends TestCase
{
    private MockInterface&SongTypeRepositoryInterface $repository;

    private ListInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(SongTypeRepositoryInterface::class);

        $this->interactor = new ListInteractor($this->repository);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(ListUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function emptySongTypes(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([])
            ->once();

        $response = $this->interactor->handle();

        $this->assertInstanceOf(ListOutputData::class, $response);

        $this->assertCount(0, $response->songTypes);
    }

    #[Test]
    public function nonEmptySongTypes(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([
                new SongType(
                    new SongTypeId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                    new SongTypeName('楽曲種別'),
                    new OrderNo(1)
                ),
            ])
            ->once();

        $response = $this->interactor->handle();

        $this->assertInstanceOf(ListOutputData::class, $response);

        $this->assertCount(1, $response->songTypes);

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $response->songTypes[0]->songTypeId->value);
        $this->assertSame('楽曲種別', $response->songTypes[0]->songTypeName->value);
        $this->assertSame(1, $response->songTypes[0]->orderNo->value);
    }
}
