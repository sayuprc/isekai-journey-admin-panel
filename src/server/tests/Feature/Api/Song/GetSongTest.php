<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Song;

use Person\Infrastructures\PersonRepository;
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
        $lyricist = $this->createPerson($lyricistId = $this->generateUuid(), '作詞者A', 1);
        $composer = $this->createPerson($composerId = $this->generateUuid(), '作曲者A', 1);
        $arranger = $this->createPerson($arrangerId = $this->generateUuid(), '編曲者A', 1);

        $personRepo = $this->app->make(PersonRepository::class);
        $personRepo->save($lyricist);
        $personRepo->save($composer);
        $personRepo->save($arranger);
        $tagRepo = $this->app->make(SongTagRepository::class);
        $tagRepo->save($tagA = $this->createSongTag($this->generateUuid(), 'タグA', 20));
        $tagRepo->save($tagB = $this->createSongTag($this->generateUuid(), 'タグB', 10));

        $songId = $this->generateUuid();

        $this->app->make(SongRepository::class)->save(
            $this->createSong(
                $songId,
                '描き続けた君へ',
                'オリジナル楽曲',
                'https://example.com/lyrics',
                SongType::Original,
                true,
                1,
                [
                    ['songTagId' => $tagA->songTagId->value],
                    ['songTagId' => $tagB->songTagId->value],
                ],
                [
                    ['personId' => $lyricistId, 'role' => 'lyricist', 'orderNo' => 1],
                    ['personId' => $composerId, 'role' => 'composer', 'orderNo' => 2],
                    ['personId' => $arrangerId, 'role' => 'arranger', 'orderNo' => 3],
                ],
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
                    'lyricsLink' => 'https://example.com/lyrics',
                    'type' => [
                        'name' => 'オリジナル曲',
                        'value' => 1,
                    ],
                    'isDisplay' => true,
                    'orderNo' => 1,
                    'persons' => [
                        ['personId' => $lyricistId, 'name' => '作詞者A', 'role' => 'lyricist', 'orderNo' => 1],
                        ['personId' => $composerId, 'name' => '作曲者A', 'role' => 'composer', 'orderNo' => 2],
                        ['personId' => $arrangerId, 'name' => '編曲者A', 'role' => 'arranger', 'orderNo' => 3],
                    ],
                    'tags' => [
                        ['songTagId' => $tagB->songTagId->value, 'name' => 'タグB'],
                        ['songTagId' => $tagA->songTagId->value, 'name' => 'タグA'],
                    ],
                ],
            ]);
    }

    #[Test]
    public function returnsNullLyricsLinkWhenUnset(): void
    {
        $songId = $this->generateUuid();

        $this->app->make(SongRepository::class)->save(
            $this->createSong($songId, '描き続けた君へ', 'オリジナル楽曲', null, SongType::Original, true, 1, [], []),
        );

        $this->withAuth()
            ->get(route(SongRouteMap::Get, $songId))
            ->assertStatus(200)
            ->assertJsonPath('song.lyricsLink', null);
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
