<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Viewer\V1\Song;

use Media\Domain\Models\MediaFormat;
use Media\Domain\Models\MediaType;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Song\Route\ViewerSongRouteMap;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class ListSongTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function showPublicSongs(): void
    {
        $visibleSongId = $this->generateUuid();
        $secondSongId = $this->generateUuid();
        $hiddenSongId = $this->generateUuid();
        $visibleMediaId = $this->generateUuid();
        $hiddenMediaId = $this->generateUuid();

        $this->storeMedia(
            $this->createMedia($visibleMediaId, '公開 MV', 'https://example.com/public', MediaType::Video, true, MediaFormat::Mv),
            $this->createMedia($hiddenMediaId, '非公開 MV', 'https://example.com/private', MediaType::Video, false, MediaFormat::Mv),
        );

        $this->storeSongs(
            $this->createSong(
                $visibleSongId,
                '描き続けた君へ',
                'Viewer の一覧表示向けに集約された楽曲説明',
                SongType::Original,
                true,
                1,
                [],
                [],
                [],
                [],
                [],
                [
                    ['mediaId' => $visibleMediaId, 'orderNo' => 1],
                    ['mediaId' => $hiddenMediaId, 'orderNo' => 2],
                ],
            ),
            $this->createSong(
                $secondSongId,
                '海月のうた',
                '2 曲目',
                SongType::Cover,
                true,
                2,
                [],
                [],
                [],
                [],
                [],
                [
                    ['mediaId' => $visibleMediaId, 'orderNo' => 1],
                ],
            ),
            $this->createSong(
                $hiddenSongId,
                '全部夢だった！',
                '非公開楽曲',
                SongType::Cover,
                false,
                2,
                [],
                [],
                [],
                [],
                [],
                [],
            ),
        );

        $response = $this->get(route(ViewerSongRouteMap::List, ['limit' => 1]))
            ->assertStatus(200)
            ->assertExactJson([
                'songs' => [
                    [
                        'songId' => $visibleSongId,
                        'title' => '描き続けた君へ',
                        'type' => [
                            'name' => 'オリジナル曲',
                            'value' => 1,
                        ],
                        'description' => 'Viewer の一覧表示向けに集約された楽曲説明',
                        'counts' => [
                            'mediaCount' => 1,
                        ],
                    ],
                ],
                'nextCursor' => base64_encode((string)json_encode([
                    'orderNo' => 1,
                    'songId' => $visibleSongId,
                ], JSON_THROW_ON_ERROR)),
            ]);

        $cursor = $response->json('nextCursor');

        $this->assertIsString($cursor);

        $this->get(route(ViewerSongRouteMap::List, ['limit' => 1, 'cursor' => $cursor]))
            ->assertStatus(200)
            ->assertExactJson([
                'songs' => [
                    [
                        'songId' => $secondSongId,
                        'title' => '海月のうた',
                        'type' => [
                            'name' => 'カバー曲',
                            'value' => 2,
                        ],
                        'description' => '2 曲目',
                        'counts' => [
                            'mediaCount' => 1,
                        ],
                    ],
                ],
            ]);
    }
}
