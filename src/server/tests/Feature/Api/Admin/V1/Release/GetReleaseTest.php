<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Release;

use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\MediumFormat;
use Release\Domain\Models\ReleaseGroupType;
use Release\Route\ReleaseRouteMap;
use Song\Domain\Models\SongType;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class GetReleaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function found(): void
    {
        $songId = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();
        $releaseId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId, 'テスト楽曲1', '説明', SongType::Original, true, 10),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );
        $this->storeReleases(
            $this->createRelease(
                $releaseId,
                $releaseGroupId,
                '初回限定盤',
                true,
                jacketArtUrl: 'https://example.com/jacket.png',
                orderNo: 10,
                media: [
                    [
                        'position' => 1,
                        'format' => MediumFormat::Cd->value,
                        'tracks' => [['songId' => $songId, 'trackNo' => 1]],
                    ],
                ],
            ),
        );

        $this->withAuth()
            ->getJson(route(ReleaseRouteMap::Get, $releaseId))
            ->assertStatus(200)
            ->assertExactJson([
                'release' => [
                    'releaseId' => $releaseId,
                    'releaseGroupId' => $releaseGroupId,
                    'name' => '初回限定盤',
                    'releasedOn' => '2024-01-01',
                    'description' => 'テスト用リリース',
                    'jacketArtUrl' => 'https://example.com/jacket.png',
                    'isDisplay' => true,
                    'orderNo' => 10,
                    'media' => [
                        [
                            'position' => 1,
                            'formatValue' => MediumFormat::Cd->value,
                            'tracks' => [
                                [
                                    'songId' => $songId,
                                    'trackNo' => 1,
                                ],
                            ],
                        ],
                    ],
                ],
                'songs' => [
                    [
                        'mediumPosition' => 1,
                        'trackNo' => 1,
                        'songId' => $songId,
                        'title' => 'テスト楽曲1',
                    ],
                ],
            ]);
    }

    #[Test]
    public function notFound(): void
    {
        $this->withAuth()
            ->getJson(route(ReleaseRouteMap::Get, $this->generateUuid()))
            ->assertStatus(404);
    }

    #[Test]
    public function invalidId(): void
    {
        $this->withAuth()
            ->getJson('/api/admin/v1/releases/invalid-id')
            ->assertStatus(404);
    }
}
