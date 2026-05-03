<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Song;

use Creator\Infrastructures\CreatorRepository;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Song\Infrastructures\Tag\SongTagRepository;
use Song\Route\SongRouteMap;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class CreateSongTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canCreate(): void
    {
        $creatorRepo = $this->app->make(CreatorRepository::class);
        $creatorRepo->save($creator1 = $this->createCreator($this->generateUuid(), '作詞者', 1));
        $creatorRepo->save($creator2 = $this->createCreator($this->generateUuid(), '作曲者', 1));
        $creatorRepo->save($creator3 = $this->createCreator($this->generateUuid(), '編曲者', 1));
        $tagRepo = $this->app->make(SongTagRepository::class);
        $tagRepo->save($tag1 = $this->createSongTag($this->generateUuid(), 'タグA', 10));
        $tagRepo->save($tag2 = $this->createSongTag($this->generateUuid(), 'タグB', 20));

        $this->withAuth()
            ->postJson(route(SongRouteMap::Create), [
                'title' => '描き続けた君へ',
                'description' => 'オリジナル楽曲',
                'typeValue' => SongType::Original->value,
                'isDisplay' => true,
                'lyricists' => [['creatorId' => $creator1->creatorId->value]],
                'composers' => [['creatorId' => $creator2->creatorId->value]],
                'arrangers' => [['creatorId' => $creator3->creatorId->value]],
                'tags' => [
                    ['songTagId' => $tag2->songTagId->value],
                    ['songTagId' => $tag1->songTagId->value],
                ],
            ])->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json
                    ->has(
                        'song',
                        fn (AssertableJson $json) => $json
                            ->whereType('songId', 'string')
                            ->where('title', '描き続けた君へ')
                            ->where('description', 'オリジナル楽曲')
                            ->where('type', [
                                'name' => SongType::Original->getName(),
                                'value' => SongType::Original->value,
                            ])
                            ->where('isDisplay', true)
                            ->where('orderNo', 10)
                            ->where('lyricists', [[
                                'creatorId' => $creator1->creatorId->value,
                                'name' => $creator1->name->value,
                                'orderNo' => 1,
                            ]])
                            ->where('composers', [[
                                'creatorId' => $creator2->creatorId->value,
                                'name' => $creator2->name->value,
                                'orderNo' => 1,
                            ]])
                            ->where('arrangers', [[
                                'creatorId' => $creator3->creatorId->value,
                                'name' => $creator3->name->value,
                                'orderNo' => 1,
                            ]])
                            ->where('tags', [[
                                'songTagId' => $tag1->songTagId->value,
                                'name' => $tag1->name->value,
                            ], [
                                'songTagId' => $tag2->songTagId->value,
                                'name' => $tag2->name->value,
                            ]]),
                    ),
            );
    }

    #[Test]
    public function createFailsWithNotExistsSongTag(): void
    {
        $this->withAuth()
            ->postJson(route(SongRouteMap::Create), [
                'title' => '描き続けた君へ',
                'description' => 'オリジナル楽曲',
                'typeValue' => SongType::Original->value,
                'isDisplay' => true,
                'lyricists' => [],
                'composers' => [],
                'arrangers' => [],
                'tags' => [['songTagId' => $this->generateUuid()]],
            ])->assertStatus(400);
    }

    #[Test]
    public function createFailsWithDuplicateSongTag(): void
    {
        $tagRepo = $this->app->make(SongTagRepository::class);
        $tagRepo->save($tag = $this->createSongTag($this->generateUuid(), 'タグA', 10));

        $this->withAuth()
            ->postJson(route(SongRouteMap::Create), [
                'title' => '描き続けた君へ',
                'description' => 'オリジナル楽曲',
                'typeValue' => SongType::Original->value,
                'isDisplay' => true,
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
        $this->markTestSkipped('実装する');
    }
}
