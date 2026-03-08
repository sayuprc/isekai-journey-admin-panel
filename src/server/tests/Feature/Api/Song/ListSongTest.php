<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Song;

use Creator\Infrastructures\CreatorRepository;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Song\Infrastructures\SongRepository;
use Song\Route\SongRouteMap;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class ListSongTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function showList(): void
    {
        $lyricist = $this->createCreator($lyricistId = $this->generateUuid(), '作詞者A', 1);
        $composer = $this->createCreator($composerId = $this->generateUuid(), '作曲者A', 1);
        $arranger = $this->createCreator($arrangerId = $this->generateUuid(), '編曲者A', 1);

        $creatorRepo = $this->app->make(CreatorRepository::class);
        $creatorRepo->save($lyricist);
        $creatorRepo->save($composer);
        $creatorRepo->save($arranger);

        $song1Id = $this->generateUuid();
        $song2Id = $this->generateUuid();

        $songRepo = $this->app->make(SongRepository::class);
        $songRepo->save(
            $this->createSong(
                $song1Id,
                '描き続けた君へ',
                'オリジナル楽曲',
                SongType::Original,
                null,
                1,
                [['creatorId' => $lyricistId, 'orderNo' => 1]],
                [['creatorId' => $composerId, 'orderNo' => 1]],
                [['creatorId' => $arrangerId, 'orderNo' => 1]],
            ),
        );
        $songRepo->save(
            $this->createSong(
                $song2Id,
                '全部夢だった！',
                'カバー楽曲',
                SongType::Cover,
                null,
                2,
                [['creatorId' => $lyricistId, 'orderNo' => 1]],
                [['creatorId' => $composerId, 'orderNo' => 1]],
                [],
            ),
        );

        $this->withAuth()
            ->get(route(SongRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson([
                'songs' => [
                    [
                        'songId' => $song1Id,
                        'title' => '描き続けた君へ',
                        'description' => 'オリジナル楽曲',
                        'type' => [
                            'name' => 'オリジナル曲',
                            'value' => 1,
                        ],
                        'orderNo' => 1,
                        'lyricists' => [['creatorId' => $lyricistId, 'name' => '作詞者A', 'orderNo' => 1]],
                        'composers' => [['creatorId' => $composerId, 'name' => '作曲者A', 'orderNo' => 1]],
                        'arrangers' => [['creatorId' => $arrangerId, 'name' => '編曲者A', 'orderNo' => 1]],
                    ],
                    [
                        'songId' => $song2Id,
                        'title' => '全部夢だった！',
                        'description' => 'カバー楽曲',
                        'type' => [
                            'name' => 'カバー曲',
                            'value' => 2,
                        ],
                        'orderNo' => 2,
                        'lyricists' => [['creatorId' => $lyricistId, 'name' => '作詞者A', 'orderNo' => 1]],
                        'composers' => [['creatorId' => $composerId, 'name' => '作曲者A', 'orderNo' => 1]],
                        'arrangers' => [],
                    ],
                ],
            ]);
    }

    #[Test]
    public function showEmptyList(): void
    {
        $this->withAuth()
            ->get(route(SongRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson(['songs' => []]);
    }
}
