<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Release;

use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseDistributionType;
use Release\Domain\Models\ReleaseType;
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
        $releaseId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId, '一曲目', '説明', SongType::Original, true, 10),
        );
        $this->storeReleases(
            $this->createRelease(
                $releaseId,
                '観測された春',
                ReleaseType::Album,
                ReleaseDistributionType::Digital,
                true,
                trackEntries: [
                    ['songId' => $songId, 'trackNo' => 1],
                ],
            ),
        );

        $this->withAuth()
            ->getJson(route(ReleaseRouteMap::Get, $releaseId))
            ->assertStatus(200)
            ->assertExactJson([
                'release' => [
                    'releaseId' => $releaseId,
                    'title' => '観測された春',
                    'typeValue' => 2,
                    'distributionTypeValue' => 1,
                    'releasedOn' => '2024-01-01',
                    'description' => 'テスト用リリース',
                    'isDisplay' => true,
                    'trackEntries' => [
                        [
                            'songId' => $songId,
                            'trackNo' => 1,
                        ],
                    ],
                ],
                'songs' => [
                    [
                        'songId' => $songId,
                        'title' => '一曲目',
                        'trackNo' => 1,
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
