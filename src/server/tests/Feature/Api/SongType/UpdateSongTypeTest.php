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

class UpdateSongTypeTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function canUpdate(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(
            FileSongTypeRepository::class,
            $uuid,
            new SongType(new SongTypeId($uuid), new SongTypeName('オリジナル'), new OrderNo(2))
        );

        $this->putJson(route(SongTypeRouteMap::Update, $uuid), [
            'songTypeName' => 'カバー',
            'orderNo' => 1,
        ])->assertStatus(200)
            ->assertJson([
                'songType' => [
                    'songTypeId' => $uuid,
                    'songTypeName' => 'カバー',
                    'orderNo' => 1,
                ],
            ]);
    }

    #[Test]
    public function updateFails(): void
    {
        $this->markTestSkipped('TODO 実装する');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->markTestSkipped('TODO 実装する');
    }
}
