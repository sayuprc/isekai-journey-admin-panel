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

class GetMediaTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function showPublicMedia(): void
    {
        $mediaId = $this->generateUuid();
        $firstSongId = $this->generateUuid();
        $secondSongId = $this->generateUuid();
        $hiddenSongId = $this->generateUuid();

        $this->storeMedia(
            $this->createMedia(
                $mediaId,
                '公開 MV',
                'https://example.com/public',
                MediaType::Video,
                true,
                MediaFormat::Mv,
                new ImmutableDate('2024-03-01'),
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
                    ['mediaId' => $mediaId, 'orderNo' => 1],
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
                    ['mediaId' => $mediaId, 'orderNo' => 2],
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
                    ['mediaId' => $mediaId, 'orderNo' => 3],
                ],
            ),
        );

        $this->get(route(ViewerMediaRouteMap::Get, ['mediaId' => $mediaId]))
            ->assertStatus(200)
            ->assertExactJson([
                'media' => [
                    'mediaId' => $mediaId,
                    'title' => '公開 MV',
                    'url' => 'https://example.com/public',
                    'publishedAt' => '2024-03-01',
                    'type' => [
                        'name' => '動画',
                        'value' => 1,
                    ],
                    'format' => [
                        'name' => 'MV',
                        'value' => 1,
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
            ]);
    }

    #[Test]
    public function notFoundWhenMediaIsHidden(): void
    {
        $mediaId = $this->generateUuid();

        $this->storeMedia(
            $this->createMedia(
                $mediaId,
                '非公開 MV',
                'https://example.com/private',
                MediaType::Video,
                false,
                MediaFormat::Mv,
                new ImmutableDate('2024-04-01'),
            ),
        );

        $this->get(route(ViewerMediaRouteMap::Get, ['mediaId' => $mediaId]))
            ->assertStatus(404);
    }

    #[Test]
    public function notFoundWhenMediaDoesNotExist(): void
    {
        $mediaId = $this->generateUuid();

        $this->get(route(ViewerMediaRouteMap::Get, ['mediaId' => $mediaId]))
            ->assertStatus(404);
    }
}
