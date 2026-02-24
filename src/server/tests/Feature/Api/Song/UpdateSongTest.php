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

class UpdateSongTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;
    use WithAuth;

    #[Test]
    public function canUpdate(): void
    {
        $creator1 = $this->createCreator($this->generateUuid(), '編曲者');
        $creator2 = $this->createCreator($this->generateUuid(), '作曲者');
        $creator3 = $this->createCreator($this->generateUuid(), '作詞者');

        $this->storeCreators($creator1, $creator2, $creator3);

        $songId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong(
                $songId,
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
            ->putJson(route(SongRouteMap::Update, $songId), [
                'title' => '描き続けた君へ',
                'description' => 'オリジナル楽曲',
                'songTypeValue' => SongType::Cover->value,
                'orderNo' => 2,
                'arrangers' => [['creatorId' => $creator1->creatorId->value, 'orderNo' => 1]],
                'composers' => [['creatorId' => $creator2->creatorId->value, 'orderNo' => 1]],
                'lyricists' => [],
            ])->assertStatus(200)
            ->assertExactJson([
                'song' => [
                    'songId' => $songId,
                    'title' => '描き続けた君へ',
                    'description' => 'オリジナル楽曲',
                    'songType' => [
                        'name' => SongType::Cover->getName(),
                        'value' => SongType::Cover->value,
                    ],
                    'orderNo' => 2,
                    'arrangers' => [
                        [
                            'creatorId' => $creator1->creatorId->value,
                            'name' => $creator1->name->value,
                            'orderNo' => 1,
                        ],
                    ],
                    'composers' => [
                        [
                            'creatorId' => $creator2->creatorId->value,
                            'name' => $creator2->name->value,
                            'orderNo' => 1,
                        ],
                    ],
                    'lyricists' => [],
                ],
            ]);
    }

    #[Test]
    public function updateFails(): void
    {
        $songId = $this->generateUuid();
        $this->storeSongs(
            $this->createSong(
                $songId,
                '曲名',
                '説明',
                SongType::Original,
                1,
                [],
                [],
                [],
            ),
        );

        $this->withAuth()
            ->putJson(route(SongRouteMap::Update, $songId), [
                'title' => '',
                'description' => '',
                'songTypeValue' => 99,
                'orderNo' => 0,
                'arrangers' => [],
                'composers' => [],
                'lyricists' => [],
            ])->assertStatus(422);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $songId = $this->generateUuid();
        $this->storeSongs(
            $this->createSong(
                $songId,
                '曲名',
                '説明',
                SongType::Original,
                1,
                [],
                [],
                [],
            ),
        );

        $this->withAuth()
            ->putJson(route(SongRouteMap::Update, $songId), [])
            ->assertStatus(422);
    }
}
