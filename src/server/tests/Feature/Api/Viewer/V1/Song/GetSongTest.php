<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Viewer\V1\Song;

use DateType\ImmutableDate;
use Media\Domain\Models\MediaFormat;
use Media\Domain\Models\MediaType;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Persons\SongPersonRole;
use Song\Domain\Models\SongType;
use Song\Route\ViewerSongRouteMap;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class GetSongTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function showPublicSong(): void
    {
        $songId = $this->generateUuid();
        $visibleMediaId = $this->generateUuid();
        $hiddenMediaId = $this->generateUuid();
        $secondVisibleMediaId = $this->generateUuid();
        $lyricistId = $this->generateUuid();
        $composerId = $this->generateUuid();
        $arrangerId = $this->generateUuid();

        $this->storePersons(
            $this->createPerson($lyricistId, '作詞太郎', 1),
            $this->createPerson($composerId, '作曲花子', 2),
            $this->createPerson($arrangerId, '編曲次郎', 3),
        );

        $this->storeMedia(
            $this->createMedia(
                $visibleMediaId,
                '公開 MV',
                'https://example.com/public',
                MediaType::Video,
                true,
                MediaFormat::Mv,
                new ImmutableDate('2024-03-01'),
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
            $this->createMedia(
                $secondVisibleMediaId,
                '公開記事',
                'https://example.com/article',
                MediaType::Article,
                true,
                MediaFormat::Other,
                new ImmutableDate('2024-05-01'),
            ),
        );

        $this->storeSongs(
            $this->createSong(
                $songId,
                '描き続けた君へ',
                'Viewer の詳細表示向け楽曲説明',
                SongType::Original,
                true,
                1,
                [],
                [
                    ['personId' => $lyricistId, 'role' => SongPersonRole::Lyricist->value, 'orderNo' => 1],
                    ['personId' => $composerId, 'role' => SongPersonRole::Composer->value, 'orderNo' => 2],
                    ['personId' => $arrangerId, 'role' => SongPersonRole::Arranger->value, 'orderNo' => 3],
                ],
                [],
                [],
                [],
                [
                    ['mediaId' => $secondVisibleMediaId, 'orderNo' => 2],
                    ['mediaId' => $hiddenMediaId, 'orderNo' => 3],
                    ['mediaId' => $visibleMediaId, 'orderNo' => 1],
                ],
            ),
        );

        $this->get(route(ViewerSongRouteMap::Get, ['songId' => $songId]))
            ->assertStatus(200)
            ->assertExactJson([
                'song' => [
                    'songId' => $songId,
                    'title' => '描き続けた君へ',
                    'description' => 'Viewer の詳細表示向け楽曲説明',
                    'type' => [
                        'name' => 'オリジナル曲',
                        'value' => 1,
                    ],
                    'counts' => [
                        'mediaCount' => 2,
                    ],
                    'lyricists' => ['作詞太郎'],
                    'composers' => ['作曲花子'],
                    'arrangers' => ['編曲次郎'],
                    'media' => [
                        [
                            'mediaId' => $visibleMediaId,
                            'title' => '公開 MV',
                            'type' => [
                                'name' => '動画',
                                'value' => 1,
                            ],
                            'format' => [
                                'name' => 'MV',
                                'value' => 1,
                            ],
                            'publishedAt' => '2024-03-01',
                        ],
                        [
                            'mediaId' => $secondVisibleMediaId,
                            'title' => '公開記事',
                            'type' => [
                                'name' => '記事',
                                'value' => 2,
                            ],
                            'format' => [
                                'name' => 'その他',
                                'value' => 99,
                            ],
                            'publishedAt' => '2024-05-01',
                        ],
                    ],
                ],
            ]);
    }

    #[Test]
    public function notFoundWhenSongIsHidden(): void
    {
        $songId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong(
                $songId,
                '非公開楽曲',
                '非公開楽曲の説明',
                SongType::Original,
                false,
                1,
                [],
                [],
                [],
                [],
                [],
            ),
        );

        $this->get(route(ViewerSongRouteMap::Get, ['songId' => $songId]))
            ->assertStatus(404);
    }

    #[Test]
    public function notFoundWhenSongDoesNotExist(): void
    {
        $songId = $this->generateUuid();

        $this->get(route(ViewerSongRouteMap::Get, ['songId' => $songId]))
            ->assertStatus(404);
    }
}
