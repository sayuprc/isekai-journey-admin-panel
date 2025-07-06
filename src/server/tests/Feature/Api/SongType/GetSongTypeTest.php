<?php

declare(strict_types=1);

namespace Tests\Feature\Api\SongType;

use PHPUnit\Framework\Attributes\Test;
use SongType\DebugInfrastructures\FileSongTypeRepository;
use SongType\Domain\Models\SongType;
use SongType\Domain\Models\SongTypeId;
use SongType\Domain\Models\SongTypeName;
use SongType\Route\SongTypeRouteMap;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class GetSongTypeTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function found(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(
            FileSongTypeRepository::class,
            $uuid,
            new SongType(new SongTypeId($uuid), new SongTypeName('オリジナル'), new OrderNo(1))
        );

        $this->get(route(SongTypeRouteMap::Get, $uuid))
            ->assertStatus(200)
            ->assertJson([
                'songType' => [
                    'songTypeId' => $uuid,
                    'songTypeName' => 'オリジナル',
                    'orderNo' => 1,
                ],
            ]);
    }

    #[Test]
    public function notFound(): void
    {
        $uuid = $this->generateUuid();

        $this->get(route(SongTypeRouteMap::Get, $uuid))
            ->assertStatus(404);
    }
}
