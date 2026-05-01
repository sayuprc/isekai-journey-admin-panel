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

class UpdateSongTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canUpdate(): void
    {
        $creator1 = $this->createCreator($this->generateUuid(), '作詞者', 1);
        $creator2 = $this->createCreator($this->generateUuid(), '作曲者', 1);
        $creator3 = $this->createCreator($this->generateUuid(), '編曲者', 1);

        $creatorRepo = $this->app->make(CreatorRepository::class);
        $creatorRepo->save($creator1);
        $creatorRepo->save($creator2);
        $creatorRepo->save($creator3);
        $tagRepo = $this->app->make(SongTagRepository::class);
        $tagRepo->save($oldTag = $this->createSongTag($this->generateUuid(), '旧タグ', 10));
        $tagRepo->save($newTag = $this->createSongTag($this->generateUuid(), '新タグ', 20));

        $songId = $this->generateUuid();

        $this->app->make(SongRepository::class)->save(
            $this->createSong(
                $songId,
                '曲名',
                '説明',
                SongType::Original,
                1,
                [['creatorId' => $creator1->creatorId->value, 'orderNo' => 1]],
                [['creatorId' => $creator2->creatorId->value, 'orderNo' => 1]],
                [['creatorId' => $creator3->creatorId->value, 'orderNo' => 1]],
                true,
                [['songTagId' => $oldTag->songTagId->value, 'orderNo' => 1]],
            ),
        );

        $this->withAuth()
            ->putJson(route(SongRouteMap::Update, $songId), [
                'title' => '描き続けた君へ',
                'description' => 'オリジナル楽曲',
                'typeValue' => SongType::Cover->value,
                'isDisplay' => false,
                'orderNo' => 2,
                'lyricists' => [],
                'composers' => [['creatorId' => $creator2->creatorId->value, 'orderNo' => 1]],
                'arrangers' => [['creatorId' => $creator3->creatorId->value, 'orderNo' => 1]],
                'tags' => [['songTagId' => $newTag->songTagId->value]],
            ])->assertStatus(200)
            ->assertExactJson([
                'song' => [
                    'songId' => $songId,
                    'title' => '描き続けた君へ',
                    'description' => 'オリジナル楽曲',
                    'type' => [
                        'name' => SongType::Cover->getName(),
                        'value' => SongType::Cover->value,
                    ],
                    'isDisplay' => false,
                    'orderNo' => 2,
                    'lyricists' => [],
                    'composers' => [
                        [
                            'creatorId' => $creator2->creatorId->value,
                            'name' => $creator2->name->value,
                            'orderNo' => 1,
                        ],
                    ],
                    'arrangers' => [
                        [
                            'creatorId' => $creator3->creatorId->value,
                            'name' => $creator3->name->value,
                            'orderNo' => 1,
                        ],
                    ],
                    'tags' => [
                        [
                            'songTagId' => $newTag->songTagId->value,
                            'name' => $newTag->name->value,
                            'orderNo' => 1,
                        ],
                    ],
                ],
            ]);
    }

    #[Test]
    public function routeSongIdIsPrioritizedOverBodySongId(): void
    {
        $creator1 = $this->createCreator($this->generateUuid(), '作詞者', 1);
        $creator2 = $this->createCreator($this->generateUuid(), '作曲者', 1);
        $creator3 = $this->createCreator($this->generateUuid(), '編曲者', 1);

        $creatorRepo = $this->app->make(CreatorRepository::class);
        $creatorRepo->save($creator1);
        $creatorRepo->save($creator2);
        $creatorRepo->save($creator3);

        $routeSongId = $this->generateUuid();
        $bodySongId = $this->generateUuid();

        $this->app->make(SongRepository::class)->save(
            $this->createSong(
                $routeSongId,
                '曲名',
                '説明',
                SongType::Original,
                1,
                [['creatorId' => $creator1->creatorId->value, 'orderNo' => 1]],
                [['creatorId' => $creator2->creatorId->value, 'orderNo' => 1]],
                [['creatorId' => $creator3->creatorId->value, 'orderNo' => 1]],
            ),
        );

        $this->withAuth()
            ->putJson(route(SongRouteMap::Update, $routeSongId), [
                'songId' => $bodySongId,
                'title' => '描き続けた君へ',
                'description' => 'オリジナル楽曲',
                'typeValue' => SongType::Cover->value,
                'isDisplay' => false,
                'orderNo' => 2,
                'lyricists' => [],
                'composers' => [['creatorId' => $creator2->creatorId->value, 'orderNo' => 1]],
                'arrangers' => [['creatorId' => $creator3->creatorId->value, 'orderNo' => 1]],
                'tags' => [],
            ])->assertStatus(200)
            ->assertExactJson([
                'song' => [
                    'songId' => $routeSongId,
                    'title' => '描き続けた君へ',
                    'description' => 'オリジナル楽曲',
                    'type' => [
                        'name' => SongType::Cover->getName(),
                        'value' => SongType::Cover->value,
                    ],
                    'isDisplay' => false,
                    'orderNo' => 2,
                    'lyricists' => [],
                    'composers' => [
                        [
                            'creatorId' => $creator2->creatorId->value,
                            'name' => $creator2->name->value,
                            'orderNo' => 1,
                        ],
                    ],
                    'arrangers' => [
                        [
                            'creatorId' => $creator3->creatorId->value,
                            'name' => $creator3->name->value,
                            'orderNo' => 1,
                        ],
                    ],
                    'tags' => [],
                ],
            ]);
    }

    #[Test]
    public function updateFailsWithNotExistsSongTag(): void
    {
        $songId = $this->generateUuid();

        $this->app->make(SongRepository::class)->save(
            $this->createSong($songId, '曲名', '説明', SongType::Original, 1, [], [], []),
        );

        $this->withAuth()
            ->putJson(route(SongRouteMap::Update, $songId), [
                'title' => '描き続けた君へ',
                'description' => 'オリジナル楽曲',
                'typeValue' => SongType::Original->value,
                'isDisplay' => true,
                'orderNo' => 1,
                'lyricists' => [],
                'composers' => [],
                'arrangers' => [],
                'tags' => [['songTagId' => $this->generateUuid()]],
            ])->assertStatus(400);
    }

    #[Test]
    public function updateFailsWithDuplicateSongTag(): void
    {
        $songId = $this->generateUuid();

        $this->app->make(SongRepository::class)->save(
            $this->createSong($songId, '曲名', '説明', SongType::Original, 1, [], [], []),
        );

        $tagRepo = $this->app->make(SongTagRepository::class);
        $tagRepo->save($tag = $this->createSongTag($this->generateUuid(), 'タグA', 10));

        $this->withAuth()
            ->putJson(route(SongRouteMap::Update, $songId), [
                'title' => '描き続けた君へ',
                'description' => 'オリジナル楽曲',
                'typeValue' => SongType::Original->value,
                'isDisplay' => true,
                'orderNo' => 1,
                'lyricists' => [],
                'composers' => [],
                'arrangers' => [],
                'tags' => [
                    ['songTagId' => $tag->songTagId->value],
                    ['songTagId' => $tag->songTagId->value],
                ],
            ])->assertStatus(422);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->markTestSkipped('TODO 実装する');
    }
}
