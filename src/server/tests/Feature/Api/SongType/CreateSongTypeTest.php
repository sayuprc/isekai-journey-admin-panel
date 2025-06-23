<?php

declare(strict_types=1);

namespace Tests\Feature\Api\SongType;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use SongType\Domain\Models\SongType;
use SongType\Domain\Models\SongTypeName;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use SongType\Route\SongTypeRouteMap;
use Tests\TestCase;

class CreateSongTypeTest extends TestCase
{
    private MockInterface&SongTypeRepositoryInterface $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(SongTypeRepositoryInterface::class);

        $this->app->bind(SongTypeRepositoryInterface::class, fn (): SongTypeRepositoryInterface => $this->repository);
    }

    #[Test]
    public function canCreate(): void
    {
        $this->repository->shouldReceive('findByName')
            ->with(Mockery::on(fn (SongTypeName $arg): bool => $arg->value === '楽曲種別'))
            ->andReturnNull()
            ->once();

        $this->repository->shouldReceive('insert')
            ->with(Mockery::on(
                fn (SongType $arg): bool => $arg->songTypeName->value === '楽曲種別'
                    && $arg->orderNo->value === 1
            ))
            ->once();

        $this->postJson(route(SongTypeRouteMap::Create), [
            'songTypeName' => '楽曲種別',
            'orderNo' => 1,
        ])->assertStatus(200)
            ->assertJson([
                'songType' => [
                    'songTypeName' => '楽曲種別',
                    'orderNo' => 1,
                ],
            ]);
    }

    #[Test]
    public function createFails(): void
    {
        $this->markTestSkipped('実装する');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->markTestSkipped('実装する');
    }
}
