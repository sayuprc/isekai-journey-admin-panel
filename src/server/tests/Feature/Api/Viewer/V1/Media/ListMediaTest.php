<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Viewer\V1\Media;

use DateType\ImmutableDate;
use Media\Domain\Models\MediaFormat;
use Media\Domain\Models\MediaType;
use Media\Route\ViewerMediaRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class ListMediaTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function showPublicMedia(): void
    {
        $firstMediaId = $this->generateUuid();
        $secondMediaId = $this->generateUuid();
        $hiddenMediaId = $this->generateUuid();
        $firstSongId = $this->generateUuid();
        $secondSongId = $this->generateUuid();
        $hiddenSongId = $this->generateUuid();

        $this->storeMedia(
            $this->createMedia(
                $firstMediaId,
                '公開 MV 1',
                'https://example.com/public/1',
                MediaType::Video,
                true,
                MediaFormat::Mv,
                new ImmutableDate('2024-03-01'),
            ),
            $this->createMedia(
                $secondMediaId,
                '公開 MV 2',
                'https://example.com/public/2',
                MediaType::Article,
                true,
                MediaFormat::AudioVideo,
                new ImmutableDate('2024-02-01'),
            ),
            $this->createMedia(
                $hiddenMediaId,
                '非公開 MV',
                'https://example.com/private',
                MediaType::Video,
                false,
                MediaFormat::Mv,
                new ImmutableDate('2024-04-01'),
            ),
        );

        $this->storeSongs(
            $this->createSong(
                $firstSongId,
                '公開楽曲 1',
                '公開楽曲 1 の説明',
                SongType::Original,
                true,
                1,
                [],
                [],
                [],
                [],
                [],
                [
                    ['mediaId' => $firstMediaId, 'orderNo' => 1],
                ],
            ),
            $this->createSong(
                $secondSongId,
                '公開楽曲 2',
                '公開楽曲 2 の説明',
                SongType::Cover,
                true,
                2,
                [],
                [],
                [],
                [],
                [],
                [
                    ['mediaId' => $firstMediaId, 'orderNo' => 2],
                ],
            ),
            $this->createSong(
                $hiddenSongId,
                '非公開楽曲',
                '非公開楽曲の説明',
                SongType::Original,
                false,
                3,
                [],
                [],
                [],
                [],
                [],
                [
                    ['mediaId' => $firstMediaId, 'orderNo' => 3],
                ],
            ),
        );

        $response = $this->get(route(ViewerMediaRouteMap::List, ['limit' => 1]))
            ->assertStatus(200)
            ->assertExactJson([
                'media' => [
                    [
                        'mediaId' => $firstMediaId,
                        'title' => '公開 MV 1',
                        'url' => 'https://example.com/public/1',
                        'publishedAt' => '2024-03-01',
                        'type' => [
                            'name' => '動画',
                            'value' => 1,
                        ],
                        'format' => [
                            'name' => 'MV',
                            'value' => 1,
                        ],
                        'platform' => [
                            'name' => 'その他',
                            'value' => 99,
                        ],
                        'counts' => [
                            'songCount' => 2,
                        ],
                        'songs' => [
                            [
                                'songId' => $firstSongId,
                                'title' => '公開楽曲 1',
                                'type' => [
                                    'name' => 'オリジナル曲',
                                    'value' => 1,
                                ],
                            ],
                            [
                                'songId' => $secondSongId,
                                'title' => '公開楽曲 2',
                                'type' => [
                                    'name' => 'カバー曲',
                                    'value' => 2,
                                ],
                            ],
                        ],
                    ],
                ],
                'nextCursor' => base64_encode((string)json_encode([
                    'publishedAt' => '2024-03-01',
                    'mediaId' => $firstMediaId,
                ], JSON_THROW_ON_ERROR)),
            ]);

        $cursor = $response->json('nextCursor');

        $this->assertIsString($cursor);

        $this->get(route(ViewerMediaRouteMap::List, ['limit' => 1, 'cursor' => $cursor]))
            ->assertStatus(200)
            ->assertExactJson([
                'media' => [
                    [
                        'mediaId' => $secondMediaId,
                        'title' => '公開 MV 2',
                        'url' => 'https://example.com/public/2',
                        'publishedAt' => '2024-02-01',
                        'type' => [
                            'name' => '記事',
                            'value' => 2,
                        ],
                        'format' => [
                            'name' => '音源動画',
                            'value' => 2,
                        ],
                        'platform' => [
                            'name' => 'その他',
                            'value' => 99,
                        ],
                        'counts' => [
                            'songCount' => 0,
                        ],
                        'songs' => [],
                    ],
                ],
            ]);
    }
}
