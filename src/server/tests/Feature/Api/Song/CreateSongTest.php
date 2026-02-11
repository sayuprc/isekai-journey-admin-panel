<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Song;

use Creator\DebugInfrastructures\FileCreatorRepository;
use Creator\Domain\Models\Creator;
use PHPUnit\Framework\Attributes\Test;
use Song\Route\SongRouteMap;
use SongType\Domain\Models\SongType;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class CreateSongTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function canCreate(): void
    {
        $this->storeCreator(
            $creator1 = $this->createCreator($this->generateUuid(), '編曲者'),
            $creator2 = $this->createCreator($this->generateUuid(), '作曲者'),
            $creator3 = $this->createCreator($this->generateUuid(), '作詞者'),
        );

        $this->postJson(route(SongRouteMap::Create), [
            'title' => '描き続けた君へ',
            'description' => 'オリジナル楽曲',
            'songTypeValue' => SongType::Original->value,
            'arrangers' => [['creatorId' => $creator1->creatorId->value]],
            'composers' => [['creatorId' => $creator2->creatorId->value]],
            'lyricists' => [['creatorId' => $creator3->creatorId->value]],
        ])->assertStatus(200)
            ->assertJson([
                'song' => [
                    // ID は事前にわからないのでチェックしない
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
                    'lyricists' => [
                        [
                            'creatorId' => $creator3->creatorId->value,
                            'creatorName' => $creator3->creatorName->value,
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

    private function storeCreator(Creator ...$creators): void
    {
        array_map(
            fn (Creator $creator) => $this->factory(FileCreatorRepository::class, $creator->creatorId->value, $creator),
            $creators,
        );
    }
}
