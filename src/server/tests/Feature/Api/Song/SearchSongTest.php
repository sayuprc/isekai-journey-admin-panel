<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Song;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongAttribute;
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
            $this->createSong($uuid, '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 1, [], [], [], true),
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
                        'orderNo' => 1,
                        'isDisplay' => true,
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
            $this->createSong($uuid1, '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 1, [], [], [], true),
            $this->createSong($uuid2, '全部夢だった！', 'カバー楽曲', SongType::Cover, null, 2, [], [], [], true),
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
                        'orderNo' => 1,
                        'isDisplay' => true,
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
            $this->createSong($uuid1, '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 1, [], [], [], true),
            $this->createSong($uuid2, '全部夢だった！', 'カバー楽曲', SongType::Cover, null, 2, [], [], [], true),
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
                        'orderNo' => 1,
                        'isDisplay' => true,
                    ],
                ],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function searchByAttribute(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid1, '描き続けた君へ', 'コラボ楽曲', SongType::Original, SongAttribute::Collaboration, 1, [], [], [], true),
            $this->createSong($uuid2, '全部夢だった！', 'オリジナル楽曲', SongType::Original, null, 2, [], [], [], true),
        );

        $this->withAuth()
            ->get(route(SongRouteMap::Search, ['attribute' => SongAttribute::Collaboration->value]))
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
                        'attribute' => [
                            'name' => 'コラボ',
                            'value' => 1,
                        ],
                        'orderNo' => 1,
                        'isDisplay' => true,
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
            $this->createSong($uuid, '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 1, [], [], [], true),
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
