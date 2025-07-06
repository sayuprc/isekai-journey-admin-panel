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

class DeleteSongTypeTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(
            FileSongTypeRepository::class,
            $uuid,
            new SongType(new SongTypeId($uuid), new SongTypeName('オリジナル'), new OrderNo(1))
        );

        $this->delete(route(SongTypeRouteMap::Delete, $uuid))
            ->assertStatus(204);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->markTestSkipped('TODO 実装する');
    }
}
