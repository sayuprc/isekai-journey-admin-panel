<?php

declare(strict_types=1);

namespace Tests\Feature\Api\SongTag;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Song\Infrastructures\Tag\SongTagRepository;
use Song\Route\Tag\SongTagRouteMap;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class DeleteSongTagTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $repository = $this->app->make(SongTagRepository::class);
        $repository->save($this->createSongTag($uuid, '派生曲', 1));

        $this->withAuth()
            ->delete(route(SongTagRouteMap::Delete, $uuid))
            ->assertStatus(204);

        $this->assertNull($repository->find($this->createSongTag($uuid, '派生曲', 1)->songTagId));
    }

    #[Test]
    public function cannotDeleteWhenUsedInSong(): void
    {
        $songTagId = $this->generateUuid();
        $songTag = $this->createSongTag($songTagId, '派生曲', 1);
        $repository = $this->app->make(SongTagRepository::class);

        $this->storeSongTags($songTag);
        $this->storeSongs($this->createSong(
            $this->generateUuid(),
            '曲名',
            '説明',
            SongType::Original,
            null,
            1,
            [],
            [],
            [],
            true,
            [['songTagId' => $songTagId, 'orderNo' => 1]],
        ));

        $this->withAuth()
            ->delete(route(SongTagRouteMap::Delete, $songTagId))
            ->assertStatus(400)
            ->assertExactJson([
                'message' => 'この楽曲タグは楽曲に使用されているため削除できません',
            ]);

        $this->assertNotNull($repository->find($songTag->songTagId));
    }

    #[Test]
    public function canDeleteEvenIfTargetDoesNotExist(): void
    {
        $this->withAuth()
            ->delete(route(SongTagRouteMap::Delete, 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'))
            ->assertStatus(204);
    }

    #[Test]
    public function requiresAuthentication(): void
    {
        $this->delete(route(SongTagRouteMap::Delete, 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'))
            ->assertStatus(401);
    }
}
