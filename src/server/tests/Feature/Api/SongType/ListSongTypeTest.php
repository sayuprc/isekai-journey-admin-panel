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

class ListSongTypeTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function showList(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->factory(
            FileSongTypeRepository::class,
            $uuid1,
            new SongType(new SongTypeId($uuid1), new SongTypeName('オリジナル'), new OrderNo(1))
        );
        $this->factory(
            FileSongTypeRepository::class,
            $uuid2,
            new SongType(new SongTypeId($uuid2), new SongTypeName('カバー'), new OrderNo(2))
        );

        $this->get(route(SongTypeRouteMap::List))
            ->assertStatus(200)
            ->assertJson([
                'songTypes' => [
                    [
                        'songTypeId' => $uuid1,
                        'songTypeName' => 'オリジナル',
                        'orderNo' => 1,
                    ],
                    [
                        'songTypeId' => $uuid2,
                        'songTypeName' => 'カバー',
                        'orderNo' => 2,
                    ],
                ],
            ]);
    }

    #[Test]
    public function showEmptyList(): void
    {
        $this->get(route(SongTypeRouteMap::List))
            ->assertStatus(200)
            ->assertJson(['songTypes' => []]);
    }
}
