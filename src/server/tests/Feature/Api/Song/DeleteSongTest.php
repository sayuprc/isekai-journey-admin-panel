<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Song;

use PHPUnit\Framework\Attributes\Test;
use Song\DebugInfrastructures\FileSongRepository;
use Song\Route\SongRouteMap;
use SongType\Domain\Models\SongType;
use Tests\Feature\Api\WithAuth;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class DeleteSongTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;
    use WithAuth;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(
            FileSongRepository::class,
            $this->createSong($uuid, '', '', SongType::Original, 1, [], [], [])->toArray(),
        );

        $this->withAuth()
            ->delete(route(SongRouteMap::Delete, $uuid))
            ->assertStatus(204);
    }

    #[Test]
    public function invalidId(): void
    {
        $this->withAuth()
            ->delete(route(SongRouteMap::Delete, 'invalid-uuid'))
            ->assertStatus(422);
    }
}
