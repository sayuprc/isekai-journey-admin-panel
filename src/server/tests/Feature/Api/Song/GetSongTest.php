<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Song;

use PHPUnit\Framework\Attributes\Test;
use Song\Route\SongRouteMap;
use SongType\Domain\Models\SongType;
use Tests\Feature\Api\WithAuth;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class GetSongTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;
    use WithAuth;

    #[Test]
    public function found(): void
    {
        $lyricist = $this->createCreator($lyricistId = $this->generateUuid(), '作詞者A', 1);
        $composer = $this->createCreator($composerId = $this->generateUuid(), '作曲者A', 1);
        $arranger = $this->createCreator($arrangerId = $this->generateUuid(), '編曲者A', 1);

        $this->storeCreators($lyricist, $composer, $arranger);

        $songId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong(
                $songId,
                '描き続けた君へ',
                'オリジナル楽曲',
                SongType::Original,
                1,
                [['creatorId' => $lyricistId, 'orderNo' => 1]],
                [['creatorId' => $composerId, 'orderNo' => 1]],
                [['creatorId' => $arrangerId, 'orderNo' => 1]],
            ),
        );

        $this->withAuth()
            ->get(route(SongRouteMap::Get, $songId))
            ->assertStatus(200)
            ->assertExactJson([
                'song' => [
                    'songId' => $songId,
                    'title' => '描き続けた君へ',
                    'description' => 'オリジナル楽曲',
                    'songType' => [
                        'name' => 'オリジナル曲',
                        'value' => 1,
                    ],
                    'orderNo' => 1,
                    'lyricists' => [['creatorId' => $lyricistId, 'name' => '作詞者A', 'orderNo' => 1]],
                    'composers' => [['creatorId' => $composerId, 'name' => '作曲者A', 'orderNo' => 1]],
                    'arrangers' => [['creatorId' => $arrangerId, 'name' => '編曲者A', 'orderNo' => 1]],
                ],
            ]);
    }

    #[Test]
    public function notFound(): void
    {
        $uuid = $this->generateUuid();

        $this->withAuth()
            ->get(route(SongRouteMap::Get, $uuid))
            ->assertStatus(404);
    }
}
