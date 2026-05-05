<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Song;

use App\Models\Media\Media as MediaModel;
use App\Models\Song\SongMediaLink as SongMediaLinkModel;
use Person\Infrastructures\PersonRepository;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Song\Infrastructures\SongRepository;
use Song\Infrastructures\Tag\SongTagRepository;
use Song\Route\SongRouteMap;
use Support\Contracts\Uuid\UuidConverterInterface;
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
        $mediaId = $this->generateUuid();

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
                    ['personId' => $lyricistId, 'role' => 1, 'orderNo' => 1],
                    ['personId' => $composerId, 'role' => 2, 'orderNo' => 2],
                    ['personId' => $arrangerId, 'role' => 3, 'orderNo' => 3],
                ],
            ),
        );

        $converter = $this->app->make(UuidConverterInterface::class);

        MediaModel::query()->insert([
            'media_id' => $converter->toBin($mediaId),
            'title' => '描き続けた君へ Official MV',
            'url' => 'https://example.com/media/1',
            'type' => 1,
            'is_display' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        SongMediaLinkModel::query()->insert([
            'song_id' => $converter->toBin($songId),
            'media_id' => $converter->toBin($mediaId),
            'song_media_type' => 1,
            'order_no' => 1,
        ]);

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
                        ['personId' => $lyricistId, 'name' => '作詞者A', 'role' => 1, 'orderNo' => 1],
                        ['personId' => $composerId, 'name' => '作曲者A', 'role' => 2, 'orderNo' => 2],
                        ['personId' => $arrangerId, 'name' => '編曲者A', 'role' => 3, 'orderNo' => 3],
                    ],
                    'tags' => [
                        ['songTagId' => $tagB->songTagId->value, 'name' => 'タグB'],
                        ['songTagId' => $tagA->songTagId->value, 'name' => 'タグA'],
                    ],
                    'media' => [
                        [
                            'mediaId' => $mediaId,
                            'title' => '描き続けた君へ Official MV',
                            'url' => 'https://example.com/media/1',
                            'type' => [
                                'name' => '動画',
                                'value' => 1,
                            ],
                            'songMediaType' => [
                                'name' => 'MV',
                                'value' => 1,
                            ],
                            'isDisplay' => true,
                            'orderNo' => 1,
                        ],
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
            ->assertJsonPath('song.lyricsLink', null)
            ->assertJsonPath('song.media', []);
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
