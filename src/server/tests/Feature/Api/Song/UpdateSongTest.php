<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Song;

use Media\Domain\Models\MediaFormat;
use Media\Domain\Models\MediaType;
use Media\Infrastructures\MediaRepository;
use Person\Infrastructures\PersonRepository;
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
        $person1 = $this->createPerson($this->generateUuid(), '作詞者', 1);
        $person2 = $this->createPerson($this->generateUuid(), '作曲者', 1);
        $person3 = $this->createPerson($this->generateUuid(), '編曲者', 1);

        $personRepo = $this->app->make(PersonRepository::class);
        $personRepo->save($person1);
        $personRepo->save($person2);
        $personRepo->save($person3);
        $tagRepo = $this->app->make(SongTagRepository::class);
        $tagRepo->save($oldTag = $this->createSongTag($this->generateUuid(), '旧タグ', 10));
        $tagRepo->save($newTag = $this->createSongTag($this->generateUuid(), '新タグ', 20));
        $mediaRepo = $this->app->make(MediaRepository::class);
        $mediaRepo->save($oldMedia = $this->createMedia($this->generateUuid(), '旧 Media', 'https://example.com/old-media', MediaType::Video, true, MediaFormat::Mv));
        $mediaRepo->save($newMedia = $this->createMedia($this->generateUuid(), '新 Media', 'https://example.com/new-media', MediaType::OfficialPage, true, MediaFormat::ShortVideo));

        $songId = $this->generateUuid();

        $this->app->make(SongRepository::class)->save(
            $this->createSong(
                $songId,
                '曲名',
                '説明',
                'https://example.com/old-lyrics',
                SongType::Original,
                true,
                1,
                [['songTagId' => $oldTag->songTagId->value]],
                [
                    ['personId' => $person1->personId->value, 'role' => 1, 'orderNo' => 1],
                    ['personId' => $person2->personId->value, 'role' => 2, 'orderNo' => 2],
                    ['personId' => $person3->personId->value, 'role' => 3, 'orderNo' => 3],
                ],
                [],
                [
                    ['mediaId' => $oldMedia->mediaId->value, 'orderNo' => 1],
                ],
            ),
        );

        $this->withAuth()
            ->putJson(route(SongRouteMap::Update, $songId), [
                'title' => '描き続けた君へ',
                'description' => 'オリジナル楽曲',
                'lyricsLink' => 'https://example.com/new-lyrics',
                'typeValue' => SongType::Cover->value,
                'isDisplay' => false,
                'orderNo' => 2,
                'persons' => [
                    ['personId' => $person2->personId->value, 'role' => 2, 'orderNo' => 1],
                    ['personId' => $person3->personId->value, 'role' => 3, 'orderNo' => 2],
                ],
                'tags' => [['songTagId' => $newTag->songTagId->value]],
                'media' => [['mediaId' => $newMedia->mediaId->value, 'orderNo' => 1]],
            ])->assertStatus(200)
            ->assertExactJson([
                'song' => [
                    'songId' => $songId,
                    'title' => '描き続けた君へ',
                    'description' => 'オリジナル楽曲',
                    'lyricsLink' => 'https://example.com/new-lyrics',
                    'type' => [
                        'name' => SongType::Cover->getName(),
                        'value' => SongType::Cover->value,
                    ],
                    'isDisplay' => false,
                    'orderNo' => 2,
                    'persons' => [
                        [
                            'personId' => $person2->personId->value,
                            'name' => $person2->name->value,
                            'role' => 2,
                            'orderNo' => 1,
                        ],
                        [
                            'personId' => $person3->personId->value,
                            'name' => $person3->name->value,
                            'role' => 3,
                            'orderNo' => 2,
                        ],
                    ],
                    'tags' => [
                        [
                            'songTagId' => $newTag->songTagId->value,
                            'name' => $newTag->name->value,
                        ],
                    ],
                    'media' => [
                        [
                            'mediaId' => $newMedia->mediaId->value,
                            'title' => $newMedia->title->value,
                            'url' => $newMedia->url->value,
                            'publishedAt' => $newMedia->publishedAt->value->format('Y-m-d'),
                            'type' => [
                                'name' => $newMedia->type->getName(),
                                'value' => $newMedia->type->value,
                            ],
                            'format' => [
                                'name' => $newMedia->format->getName(),
                                'value' => $newMedia->format->value,
                            ],
                            'isDisplay' => true,
                            'orderNo' => 1,
                        ],
                    ],
                ],
            ]);
    }

    #[Test]
    public function canResetLyricsLinkToNull(): void
    {
        $songId = $this->generateUuid();

        $this->app->make(SongRepository::class)->save(
            $this->createSong(
                $songId,
                '曲名',
                '説明',
                'https://example.com/lyrics',
                SongType::Original,
                true,
                1,
                [],
                [],
                [],
                [],
            ),
        );

        $this->withAuth()
            ->putJson(route(SongRouteMap::Update, $songId), [
                'title' => '描き続けた君へ',
                'description' => 'オリジナル楽曲',
                'lyricsLink' => null,
                'typeValue' => SongType::Original->value,
                'isDisplay' => true,
                'orderNo' => 1,
                'persons' => [],
                'tags' => [],
                'media' => [],
            ])->assertStatus(200)
            ->assertJsonPath('song.lyricsLink', null)
            ->assertJsonPath('song.media', []);
    }

    #[Test]
    public function routeSongIdIsPrioritizedOverBodySongId(): void
    {
        $person1 = $this->createPerson($this->generateUuid(), '作詞者', 1);
        $person2 = $this->createPerson($this->generateUuid(), '作曲者', 1);
        $person3 = $this->createPerson($this->generateUuid(), '編曲者', 1);

        $personRepo = $this->app->make(PersonRepository::class);
        $personRepo->save($person1);
        $personRepo->save($person2);
        $personRepo->save($person3);

        $routeSongId = $this->generateUuid();
        $bodySongId = $this->generateUuid();

        $this->app->make(SongRepository::class)->save(
            $this->createSong(
                $routeSongId,
                '曲名',
                '説明',
                null,
                SongType::Original,
                true,
                1,
                [],
                [
                    ['personId' => $person1->personId->value, 'role' => 1, 'orderNo' => 1],
                    ['personId' => $person2->personId->value, 'role' => 2, 'orderNo' => 2],
                    ['personId' => $person3->personId->value, 'role' => 3, 'orderNo' => 3],
                ],
                [],
                [],
            ),
        );

        $this->withAuth()
            ->putJson(route(SongRouteMap::Update, $routeSongId), [
                'songId' => $bodySongId,
                'title' => '描き続けた君へ',
                'description' => 'オリジナル楽曲',
                'lyricsLink' => null,
                'typeValue' => SongType::Cover->value,
                'isDisplay' => false,
                'orderNo' => 2,
                'persons' => [
                    ['personId' => $person2->personId->value, 'role' => 2, 'orderNo' => 1],
                    ['personId' => $person3->personId->value, 'role' => 3, 'orderNo' => 2],
                ],
                'tags' => [],
                'media' => [],
            ])->assertStatus(200)
            ->assertExactJson([
                'song' => [
                    'songId' => $routeSongId,
                    'title' => '描き続けた君へ',
                    'description' => 'オリジナル楽曲',
                    'lyricsLink' => null,
                    'type' => [
                        'name' => SongType::Cover->getName(),
                        'value' => SongType::Cover->value,
                    ],
                    'isDisplay' => false,
                    'orderNo' => 2,
                    'persons' => [
                        [
                            'personId' => $person2->personId->value,
                            'name' => $person2->name->value,
                            'role' => 2,
                            'orderNo' => 1,
                        ],
                        [
                            'personId' => $person3->personId->value,
                            'name' => $person3->name->value,
                            'role' => 3,
                            'orderNo' => 2,
                        ],
                    ],
                    'tags' => [],
                    'media' => [],
                ],
            ]);
    }

    #[Test]
    public function updateFailsWithNotExistsSongTag(): void
    {
        $songId = $this->generateUuid();

        $this->app->make(SongRepository::class)->save(
            $this->createSong($songId, '曲名', '説明', null, SongType::Original, true, 1, [], [], [], []),
        );

        $this->withAuth()
            ->putJson(route(SongRouteMap::Update, $songId), [
                'title' => '描き続けた君へ',
                'description' => 'オリジナル楽曲',
                'lyricsLink' => null,
                'typeValue' => SongType::Original->value,
                'isDisplay' => true,
                'orderNo' => 1,
                'persons' => [],
                'tags' => [['songTagId' => $this->generateUuid()]],
                'media' => [],
            ])->assertStatus(400);
    }

    #[Test]
    public function updateFailsWithDuplicateSongTag(): void
    {
        $songId = $this->generateUuid();

        $this->app->make(SongRepository::class)->save(
            $this->createSong($songId, '曲名', '説明', null, SongType::Original, true, 1, [], [], [], []),
        );

        $tagRepo = $this->app->make(SongTagRepository::class);
        $tagRepo->save($tag = $this->createSongTag($this->generateUuid(), 'タグA', 10));

        $this->withAuth()
            ->putJson(route(SongRouteMap::Update, $songId), [
                'title' => '描き続けた君へ',
                'description' => 'オリジナル楽曲',
                'lyricsLink' => null,
                'typeValue' => SongType::Original->value,
                'isDisplay' => true,
                'orderNo' => 1,
                'persons' => [],
                'tags' => [
                    ['songTagId' => $tag->songTagId->value],
                    ['songTagId' => $tag->songTagId->value],
                ],
                'media' => [],
            ])->assertStatus(422);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->markTestSkipped('TODO 実装する');
    }
}
