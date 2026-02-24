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

class CreateSongTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;
    use WithAuth;

    #[Test]
    public function canCreate(): void
    {
        $this->storeCreators(
            $creator1 = $this->createCreator($this->generateUuid(), '編曲者'),
            $creator2 = $this->createCreator($this->generateUuid(), '作曲者'),
            $creator3 = $this->createCreator($this->generateUuid(), '作詞者'),
        );

        $response = $this->withAuth()
            ->postJson(route(SongRouteMap::Create), [
                'title' => '描き続けた君へ',
                'description' => 'オリジナル楽曲',
                'songTypeValue' => SongType::Original->value,
                'arrangers' => [['creatorId' => $creator1->creatorId->value]],
                'composers' => [['creatorId' => $creator2->creatorId->value]],
                'lyricists' => [['creatorId' => $creator3->creatorId->value]],
            ]);

        $response->assertStatus(200);
        $songId = $response->json('song.songId');

        $response->assertExactJson([
            'song' => [
                'songId' => $songId,
                'title' => '描き続けた君へ',
                'description' => 'オリジナル楽曲',
                'songType' => [
                    'name' => SongType::Original->getName(),
                    'value' => SongType::Original->value,
                ],
                'orderNo' => 10,
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
                'lyricists' => [
                    [
                        'creatorId' => $creator3->creatorId->value,
                        'name' => $creator3->name->value,
                        'orderNo' => 1,
                    ],
                ],
            ],
        ]);
    }

    #[Test]
    public function createFails(): void
    {
        $this->markTestSkipped('実装する');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->markTestSkipped('実装する');
    }
}
