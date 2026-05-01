<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Song;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Song\Route\SongRouteMap;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchSongTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function searchAll(): void
    {
        $uuid = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid, '描き続けた君へ', 'オリジナル楽曲', SongType::Original, 1, [], [], []),
        );

        $this->withAuth()
            ->get(route(SongRouteMap::Search))
            ->assertStatus(200)
            ->assertExactJson([
                'songs' => [
                    [
                        'songId' => $uuid,
                        'title' => '描き続けた君へ',
                        'type' => [
                            'name' => 'オリジナル曲',
                            'value' => 1,
                        ],
                        'isDisplay' => true,
                        'orderNo' => 1,
                    ],
                ],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function searchByTitle(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid1, '描き続けた君へ', 'オリジナル楽曲', SongType::Original, 1, [], [], []),
            $this->createSong($uuid2, '全部夢だった！', 'カバー楽曲', SongType::Cover, 2, [], [], []),
        );

        $this->withAuth()
            ->get(route(SongRouteMap::Search, ['title' => '描き続けた君へ']))
            ->assertStatus(200)
            ->assertExactJson([
                'songs' => [
                    [
                        'songId' => $uuid1,
                        'title' => '描き続けた君へ',
                        'type' => [
                            'name' => 'オリジナル曲',
                            'value' => 1,
                        ],
                        'isDisplay' => true,
                        'orderNo' => 1,
                    ],
                ],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function searchByType(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid1, '描き続けた君へ', 'オリジナル楽曲', SongType::Original, 1, [], [], []),
            $this->createSong($uuid2, '全部夢だった！', 'カバー楽曲', SongType::Cover, 2, [], [], []),
        );

        $this->withAuth()
            ->get(route(SongRouteMap::Search, ['type' => SongType::Original->value]))
            ->assertStatus(200)
            ->assertExactJson([
                'songs' => [
                    [
                        'songId' => $uuid1,
                        'title' => '描き続けた君へ',
                        'type' => [
                            'name' => 'オリジナル曲',
                            'value' => 1,
                        ],
                        'isDisplay' => true,
                        'orderNo' => 1,
                    ],
                ],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function searchByIsDisplay(): void
    {
        $displaySongId = $this->generateUuid();
        $hiddenSongId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($displaySongId, '描き続けた君へ', 'オリジナル楽曲', SongType::Original, 1, [], [], []),
            $this->createSong($hiddenSongId, '全部夢だった！', 'カバー楽曲', SongType::Cover, 2, [], [], [], false),
        );

        $this->withAuth()
            ->get(route(SongRouteMap::Search, ['is_display' => false]))
            ->assertStatus(200)
            ->assertExactJson([
                'songs' => [
                    [
                        'songId' => $hiddenSongId,
                        'title' => '全部夢だった！',
                        'type' => [
                            'name' => 'カバー曲',
                            'value' => 2,
                        ],
                        'isDisplay' => false,
                        'orderNo' => 2,
                    ],
                ],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function searchByTitleNotFound(): void
    {
        $uuid = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid, '描き続けた君へ', 'オリジナル楽曲', SongType::Original, 1, [], [], []),
        );

        $this->withAuth()
            ->get(route(SongRouteMap::Search, ['title' => '存在しないタイトル']))
            ->assertStatus(200)
            ->assertExactJson([
                'songs' => [],
                'maxPage' => 0,
            ]);
    }
}
