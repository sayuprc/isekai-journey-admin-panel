<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Song;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongType;
use Song\Infrastructures\SongRepository;
use Song\Route\SongRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class ResetSongOrderNumbersTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function resetsOrderNumbersInStepsOfTen(): void
    {
        $repository = $this->app->make(SongRepository::class);

        $id1 = $this->generateUuid();
        $id2 = $this->generateUuid();

        $repository->save($this->createSong($id1, '曲A', '説明', SongType::Original, true, 4));
        $repository->save($this->createSong($id2, '曲B', '説明', SongType::Original, true, 9));

        $this->withAuth()
            ->postJson(route(SongRouteMap::ResetOrderNumbers))
            ->assertStatus(200)
            ->assertExactJson([
                'updatedCount' => 2,
            ]);

        $this->assertSame(10, $repository->find(SongId::reconstruct($id1))?->orderNo->value);
        $this->assertSame(20, $repository->find(SongId::reconstruct($id2))?->orderNo->value);
    }
}
