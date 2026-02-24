<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Song;

use Illuminate\Testing\Fluent\AssertableJson;
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

        $this->withAuth()
            ->postJson(route(SongRouteMap::Create), [
                'title' => '描き続けた君へ',
                'description' => 'オリジナル楽曲',
                'songTypeValue' => SongType::Original->value,
                'arrangers' => [['creatorId' => $creator1->creatorId->value]],
                'composers' => [['creatorId' => $creator2->creatorId->value]],
                'lyricists' => [['creatorId' => $creator3->creatorId->value]],
            ])->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json
                    ->has(
                        'song',
                        fn (AssertableJson $json) => $json
                            ->whereType('songId', 'string')
                            ->where('title', '描き続けた君へ')
                            ->where('description', 'オリジナル楽曲')
                            ->where('songType', [
                                'name' => SongType::Original->getName(),
                                'value' => SongType::Original->value,
                            ])
                            ->where('orderNo', 10)
                            ->where('arrangers', [[
                                'creatorId' => $creator1->creatorId->value,
                                'name' => $creator1->name->value,
                                'orderNo' => 1,
                            ]])
                            ->where('composers', [[
                                'creatorId' => $creator2->creatorId->value,
                                'name' => $creator2->name->value,
                                'orderNo' => 1,
                            ]])
                            ->where('lyricists', [[
                                'creatorId' => $creator3->creatorId->value,
                                'name' => $creator3->name->value,
                                'orderNo' => 1,
                            ]]),
                    ),
            );
    }

    #[Test]
    public function createFails(): void
    {
        $this->withAuth()
            ->postJson(route(SongRouteMap::Create), [
                'title' => '',
                'description' => '',
                'songTypeValue' => 99,
                'arrangers' => [],
                'composers' => [],
                'lyricists' => [],
            ])->assertStatus(422);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->withAuth()
            ->postJson(route(SongRouteMap::Create), [])
            ->assertStatus(422);
    }
}
