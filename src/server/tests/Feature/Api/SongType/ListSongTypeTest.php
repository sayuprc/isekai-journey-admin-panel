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

class ListSongTypeTest extends TestCase
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
    public function showList(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('all')
            ->andReturn([
                new SongType(
                    new SongTypeId($uuid),
                    new SongTypeName('楽曲種別1'),
                    new OrderNo(1),
                ),
                new SongType(
                    new SongTypeId($uuid),
                    new SongTypeName('楽曲種別2'),
                    new OrderNo(2),
                ),
            ])
            ->once();

        $this->get(route(SongTypeRouteMap::List))
            ->assertStatus(200)
            ->assertJson([
                'songTypes' => [
                    [
                        'songTypeId' => $uuid,
                        'songTypeName' => '楽曲種別1',
                        'orderNo' => 1,
                    ],
                    [
                        'songTypeId' => $uuid,
                        'songTypeName' => '楽曲種別2',
                        'orderNo' => 2,
                    ],
                ],
            ]);
    }

    #[Test]
    public function showEmptyList(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([])
            ->once();

        $this->get(route(SongTypeRouteMap::List))
            ->assertStatus(200)
            ->assertJson(['songTypes' => []]);
    }
}
