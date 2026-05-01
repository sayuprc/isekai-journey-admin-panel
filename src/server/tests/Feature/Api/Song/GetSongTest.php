<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Song;

use Creator\Infrastructures\CreatorRepository;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Song\Infrastructures\SongRepository;
use Song\Infrastructures\Tag\SongTagRepository;
use Song\Route\SongRouteMap;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class GetSongTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function found(): void
    {
        $lyricist = $this->createCreator($lyricistId = $this->generateUuid(), '作詞者A', 1);
        $composer = $this->createCreator($composerId = $this->generateUuid(), '作曲者A', 1);
        $arranger = $this->createCreator($arrangerId = $this->generateUuid(), '編曲者A', 1);

        $creatorRepo = $this->app->make(CreatorRepository::class);
        $creatorRepo->save($lyricist);
        $creatorRepo->save($composer);
        $creatorRepo->save($arranger);
        $tagRepo = $this->app->make(SongTagRepository::class);
        $tagRepo->save($tag = $this->createSongTag($this->generateUuid(), 'タグA', 10));

        $songId = $this->generateUuid();

        $this->app->make(SongRepository::class)->save(
            $this->createSong(
                $songId,
                '描き続けた君へ',
                'オリジナル楽曲',
                SongType::Original,
                null,
                1,
                [['creatorId' => $lyricistId, 'orderNo' => 1]],
                [['creatorId' => $composerId, 'orderNo' => 1]],
                [['creatorId' => $arrangerId, 'orderNo' => 1]],
                true,
                [['songTagId' => $tag->songTagId->value, 'orderNo' => 1]],
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
                    'type' => [
                        'name' => 'オリジナル曲',
                        'value' => 1,
                    ],
                    'isDisplay' => true,
                    'orderNo' => 1,
                    'lyricists' => [['creatorId' => $lyricistId, 'name' => '作詞者A', 'orderNo' => 1]],
                    'composers' => [['creatorId' => $composerId, 'name' => '作曲者A', 'orderNo' => 1]],
                    'arrangers' => [['creatorId' => $arrangerId, 'name' => '編曲者A', 'orderNo' => 1]],
                    'tags' => [['songTagId' => $tag->songTagId->value, 'name' => 'タグA', 'orderNo' => 1]],
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
