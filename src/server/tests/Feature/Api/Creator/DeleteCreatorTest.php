<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Creator;

use Creator\DebugInfrastructures\FileCreatorRepository;
use Creator\Route\CreatorRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Song\DebugInfrastructures\FileSongRepository;
use SongType\Domain\Models\SongType;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class DeleteCreatorTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(FileCreatorRepository::class, $uuid, $this->createCreator($uuid, 'クリエイター'));

        $this->delete(route(CreatorRouteMap::Delete, $uuid))
            ->assertStatus(204);
    }

    #[Test]
    public function cannotDeleteWhenUsedInSong(): void
    {
        $creatorId = $this->generateUuid();
        $songId = $this->generateUuid();

        $this->factory(FileCreatorRepository::class, $creatorId, $this->createCreator($creatorId, 'クリエイター'));
        $this->factory(FileSongRepository::class, $songId, $this->createSong(
            $songId,
            '曲名',
            '説明',
            SongType::Original,
            1,
            [['creatorId' => $creatorId, 'orderNo' => 1]],
            [],
            [],
        ));

        $this->delete(route(CreatorRouteMap::Delete, $creatorId))
            ->assertStatus(400)
            ->assertJson([
                'message' => 'このクリエイターは楽曲に使用されているため削除できません',
            ]);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->markTestSkipped('TODO 実装する');
    }
}
