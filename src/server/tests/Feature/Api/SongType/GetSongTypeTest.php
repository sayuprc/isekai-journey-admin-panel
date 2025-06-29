<?php

declare(strict_types=1);

namespace Tests\Feature\Api\SongType;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use SongType\Domain\Models\SongType;
use SongType\Domain\Models\SongTypeId;
use SongType\Domain\Models\SongTypeName;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use SongType\Route\SongTypeRouteMap;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class GetSongTypeTest extends TestCase
{
    // TODO モックやめる
    private MockInterface&SongTypeRepositoryInterface $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(SongTypeRepositoryInterface::class);

        $this->app->bind(SongTypeRepositoryInterface::class, fn (): SongTypeRepositoryInterface => $this->repository);
    }

    #[Test]
    public function found(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('find')
            ->with(Mockery::on(fn (SongTypeId $arg) => $arg->value === $uuid))
            ->andReturn(new SongType(
                new SongTypeId($uuid),
                new SongTypeName('楽曲種別1'),
                new OrderNo(1),
            ))
            ->once();

        $this->get(route(SongTypeRouteMap::Get, $uuid))
            ->assertStatus(200)
            ->assertJson([
                'songType' => [
                    'songTypeId' => $uuid,
                    'songTypeName' => '楽曲種別1',
                    'orderNo' => 1,
                ],
            ]);
    }

    #[Test]
    public function notFound(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('find')
            ->with(Mockery::on(fn (SongTypeId $arg) => $arg->value === $uuid))
            ->andReturnNull()
            ->once();

        $this->get(route(SongTypeRouteMap::Get, $uuid))
            ->assertStatus(404);
    }
}
