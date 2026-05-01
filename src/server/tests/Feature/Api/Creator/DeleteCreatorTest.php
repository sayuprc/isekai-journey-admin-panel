<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Creator;

use Creator\Infrastructures\CreatorRepository;
use Creator\Route\CreatorRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Song\Infrastructures\SongRepository;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class DeleteCreatorTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->storeCreators($this->createCreator($uuid, 'クリエイター', 1));

        $this->withAuth()
            ->delete(route(CreatorRouteMap::Delete, $uuid))
            ->assertStatus(204);
    }

    #[Test]
    public function cannotDeleteWhenUsedInSong(): void
    {
        $creatorId = $this->generateUuid();
        $songId = $this->generateUuid();

        $this->app->make(CreatorRepository::class)->save($this->createCreator($creatorId, 'クリエイター', 1));
        $this->app->make(SongRepository::class)->save($this->createSong(
            $songId,
            '曲名',
            '説明',
            SongType::Original,
            1,
            [['creatorId' => $creatorId, 'orderNo' => 1]],
            [],
            [],
        ));

        $this->withAuth()
            ->delete(route(CreatorRouteMap::Delete, $creatorId))
            ->assertStatus(400)
            ->assertExactJson([
                'message' => 'このクリエイターは楽曲に使用されているため削除できません',
            ]);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->markTestSkipped('TODO 実装する');
    }
}
