<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Song;

use Creator\DebugInfrastructures\FileCreatorRepository;
use Creator\Domain\Models\Creator;
use PHPUnit\Framework\Attributes\Test;
use Song\DebugInfrastructures\FileSongRepository;
use Song\Domain\Models\Song;
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
            ->assertJson([
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
                            'creatorName' => $creator1->creatorName->value,
                            'orderNo' => 1,
                        ],
                    ],
                    'composers' => [
                        [
                            'creatorId' => $creator2->creatorId->value,
                            'creatorName' => $creator2->creatorName->value,
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
        $this->markTestSkipped('TODO 実装する');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->markTestSkipped('TODO 実装する');
    }

    private function storeSongs(Song ...$songs): void
    {
        array_map(
            fn (Song $song) => $this->factory(FileSongRepository::class, $song->songId->value, $song),
            $songs,
        );
    }

    private function storeCreators(Creator ...$creators): void
    {
        array_map(
            fn (Creator $creator) => $this->factory(FileCreatorRepository::class, $creator->creatorId->value, $creator),
            $creators,
        );
    }
}
