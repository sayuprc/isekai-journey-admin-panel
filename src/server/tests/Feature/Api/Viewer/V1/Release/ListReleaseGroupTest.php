<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Viewer\V1\Release;

use DateType\ImmutableDate;
use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\MediumFormat;
use Release\Domain\Models\ReleaseGroupType;
use Release\Route\ViewerReleaseGroupRouteMap;
use Song\Domain\Models\SongType;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class ListReleaseGroupTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function showPublicReleaseGroups(): void
    {
        $visibleSongId = $this->generateUuid();
        $hiddenSongId = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();
        $releaseId1 = $this->generateUuid();
        $releaseId2 = $this->generateUuid();
        $hiddenReleaseId = $this->generateUuid();
        $hiddenGroupId = $this->generateUuid();
        $emptyGroupId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($visibleSongId, '公開楽曲', '説明', SongType::Original, true, 10),
            $this->createSong($hiddenSongId, '非公開楽曲', '説明', SongType::Original, false, 20),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true, '1st アルバム'),
            $this->createReleaseGroup($hiddenGroupId, '非公開グループ', ReleaseGroupType::Single, false),
            $this->createReleaseGroup($emptyGroupId, '公開リリース無しグループ', ReleaseGroupType::Ep, true),
        );
        $this->storeReleases(
            $this->createRelease(
                $releaseId1,
                $releaseGroupId,
                '配信',
                true,
                new ImmutableDate('2026-05-01'),
                description: '先行配信',
                media: [
                    [
                        'position' => 1,
                        'format' => MediumFormat::Digital->value,
                        'tracks' => [
                            ['songId' => $visibleSongId, 'trackNo' => 1],
                            ['songId' => $hiddenSongId, 'trackNo' => 2],
                        ],
                    ],
                ],
            ),
            $this->createRelease(
                $releaseId2,
                $releaseGroupId,
                '初回限定盤',
                true,
                new ImmutableDate('2026-06-01'),
                description: 'CD+DVD',
                jacketArtUrl: 'https://example.com/limited.png',
                media: [
                    [
                        'position' => 1,
                        'format' => MediumFormat::Cd->value,
                        'tracks' => [['songId' => $visibleSongId, 'trackNo' => 1]],
                    ],
                    [
                        'position' => 2,
                        'format' => MediumFormat::Dvd->value,
                        'tracks' => [],
                    ],
                ],
            ),
            // 非公開リリースはグループが公開でも一覧に出ない。
            $this->createRelease($hiddenReleaseId, $releaseGroupId, '非公開盤', false, new ImmutableDate('2026-04-01')),
            // 非公開グループ・公開リリース無しグループは一覧に出ない。
            $this->createRelease($this->generateUuid(), $hiddenGroupId, '通常盤', true),
            $this->createRelease($this->generateUuid(), $emptyGroupId, '非公開盤', false),
        );

        $this->get(route(ViewerReleaseGroupRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson([
                'releaseGroups' => [
                    [
                        'releaseGroupId' => $releaseGroupId,
                        'title' => '観測された春',
                        'type' => [
                            'name' => 'アルバム',
                            'value' => 2,
                        ],
                        'description' => '1st アルバム',
                        'firstReleasedOn' => '2026-05-01',
                        'releases' => [
                            [
                                'releaseId' => $releaseId1,
                                'name' => '配信',
                                'releasedOn' => '2026-05-01',
                                'description' => '先行配信',
                                'jacketArtUrl' => null,
                                'media' => [
                                    [
                                        'position' => 1,
                                        'format' => [
                                            'name' => '配信',
                                            'value' => 1,
                                        ],
                                        'tracks' => [
                                            [
                                                'trackNo' => 1,
                                                'songId' => $visibleSongId,
                                                'title' => '公開楽曲',
                                                'isDisplay' => true,
                                            ],
                                            [
                                                'trackNo' => 2,
                                                'songId' => $hiddenSongId,
                                                'title' => '非公開楽曲',
                                                'isDisplay' => false,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'releaseId' => $releaseId2,
                                'name' => '初回限定盤',
                                'releasedOn' => '2026-06-01',
                                'description' => 'CD+DVD',
                                'jacketArtUrl' => 'https://example.com/limited.png',
                                'media' => [
                                    [
                                        'position' => 1,
                                        'format' => [
                                            'name' => 'CD',
                                            'value' => 2,
                                        ],
                                        'tracks' => [
                                            [
                                                'trackNo' => 1,
                                                'songId' => $visibleSongId,
                                                'title' => '公開楽曲',
                                                'isDisplay' => true,
                                            ],
                                        ],
                                    ],
                                    [
                                        'position' => 2,
                                        'format' => [
                                            'name' => 'DVD',
                                            'value' => 3,
                                        ],
                                        'tracks' => [],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
    }

    #[Test]
    public function paginatesWithCursor(): void
    {
        $releaseGroupId1 = $this->generateUuid();
        $releaseGroupId2 = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId1, '古い作品', ReleaseGroupType::Single, true),
            $this->createReleaseGroup($releaseGroupId2, '新しい作品', ReleaseGroupType::Album, true),
        );
        $this->storeReleases(
            $this->createRelease($this->generateUuid(), $releaseGroupId1, '配信', true, new ImmutableDate('2026-01-01')),
            $this->createRelease($this->generateUuid(), $releaseGroupId2, '配信', true, new ImmutableDate('2026-02-01')),
        );

        // 最古発売日の降順なので新しい作品が先。
        $response = $this->get(route(ViewerReleaseGroupRouteMap::List, ['limit' => 1]))
            ->assertStatus(200)
            ->assertJsonCount(1, 'releaseGroups')
            ->assertJsonPath('releaseGroups.0.releaseGroupId', $releaseGroupId2);

        $cursor = $response->json('nextCursor');
        $this->assertIsString($cursor);

        $this->get(route(ViewerReleaseGroupRouteMap::List, ['limit' => 1, 'cursor' => $cursor]))
            ->assertStatus(200)
            ->assertJsonCount(1, 'releaseGroups')
            ->assertJsonPath('releaseGroups.0.releaseGroupId', $releaseGroupId1)
            ->assertJsonMissingPath('nextCursor');
    }
}
