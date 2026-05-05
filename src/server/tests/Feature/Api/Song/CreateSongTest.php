<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Song;

use Illuminate\Testing\Fluent\AssertableJson;
use Person\Infrastructures\PersonRepository;
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
        $personRepo = $this->app->make(PersonRepository::class);
        $personRepo->save($person1 = $this->createPerson($this->generateUuid(), '作詞者', 1));
        $personRepo->save($person2 = $this->createPerson($this->generateUuid(), '作曲者', 1));
        $personRepo->save($person3 = $this->createPerson($this->generateUuid(), '編曲者', 1));
        $tagRepo = $this->app->make(SongTagRepository::class);
        $tagRepo->save($tag1 = $this->createSongTag($this->generateUuid(), 'タグA', 10));
        $tagRepo->save($tag2 = $this->createSongTag($this->generateUuid(), 'タグB', 20));

        $this->withAuth()
            ->postJson(route(SongRouteMap::Create), [
                'title' => '描き続けた君へ',
                'description' => 'オリジナル楽曲',
                'lyricsLink' => 'https://example.com/lyrics',
                'typeValue' => SongType::Original->value,
                'isDisplay' => true,
                'persons' => [
                    ['personId' => $person1->personId->value, 'role' => 1, 'orderNo' => 1],
                    ['personId' => $person2->personId->value, 'role' => 2, 'orderNo' => 2],
                    ['personId' => $person3->personId->value, 'role' => 3, 'orderNo' => 3],
                ],
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
                            ->where('lyricsLink', 'https://example.com/lyrics')
                            ->where('type', [
                                'name' => SongType::Original->getName(),
                                'value' => SongType::Original->value,
                            ])
                            ->where('isDisplay', true)
                            ->where('orderNo', 10)
                            ->where('persons', [[
                                'personId' => $person1->personId->value,
                                'name' => $person1->name->value,
                                'role' => 1,
                                'orderNo' => 1,
                            ], [
                                'personId' => $person2->personId->value,
                                'name' => $person2->name->value,
                                'role' => 2,
                                'orderNo' => 2,
                            ], [
                                'personId' => $person3->personId->value,
                                'name' => $person3->name->value,
                                'role' => 3,
                                'orderNo' => 3,
                            ]])
                            ->where('tags', [[
                                'songTagId' => $tag1->songTagId->value,
                                'name' => $tag1->name->value,
                            ], [
                                'songTagId' => $tag2->songTagId->value,
                                'name' => $tag2->name->value,
                            ]])
                            ->where('media', []),
                    ),
            );
    }

    #[Test]
    public function canCreateWithNullLyricsLink(): void
    {
        $this->withAuth()
            ->postJson(route(SongRouteMap::Create), [
                'title' => '描き続けた君へ',
                'description' => 'オリジナル楽曲',
                'lyricsLink' => null,
                'typeValue' => SongType::Original->value,
                'isDisplay' => true,
                'persons' => [],
                'tags' => [],
            ])->assertStatus(200)
            ->assertJsonPath('song.lyricsLink', null);
    }

    #[Test]
    public function createFailsWithNotExistsSongTag(): void
    {
        $this->withAuth()
            ->postJson(route(SongRouteMap::Create), [
                'title' => '描き続けた君へ',
                'description' => 'オリジナル楽曲',
                'lyricsLink' => null,
                'typeValue' => SongType::Original->value,
                'isDisplay' => true,
                'persons' => [],
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
                'lyricsLink' => null,
                'typeValue' => SongType::Original->value,
                'isDisplay' => true,
                'persons' => [],
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
