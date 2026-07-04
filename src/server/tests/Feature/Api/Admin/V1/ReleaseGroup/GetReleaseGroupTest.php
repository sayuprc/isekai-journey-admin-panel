<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\ReleaseGroup;

use DateType\ImmutableDate;
use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\MediumFormat;
use Release\Domain\Models\ReleaseGroupType;
use Release\Route\ReleaseGroupRouteMap;
use Song\Domain\Models\SongType;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class GetReleaseGroupTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function found(): void
    {
        $songId = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();
        $releaseId1 = $this->generateUuid();
        $releaseId2 = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId, 'テスト楽曲1', '説明', SongType::Original, true, 10),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true, '1st アルバム'),
        );
        $this->storeReleases(
            $this->createRelease(
                $releaseId1,
                $releaseGroupId,
                '配信',
                true,
                new ImmutableDate('2026-05-01'),
                media: [
                    [
                        'position' => 1,
                        'format' => MediumFormat::Digital->value,
                        'tracks' => [['songId' => $songId, 'trackNo' => 1]],
                    ],
                ],
            ),
            $this->createRelease(
                $releaseId2,
                $releaseGroupId,
                '初回限定盤',
                true,
                new ImmutableDate('2026-06-01'),
                media: [
                    [
                        'position' => 1,
                        'format' => MediumFormat::Cd->value,
                        'tracks' => [['songId' => $songId, 'trackNo' => 1]],
                    ],
                    [
                        'position' => 2,
                        'format' => MediumFormat::Dvd->value,
                        'tracks' => [],
                    ],
                ],
            ),
        );

        $this->withAuth()
            ->getJson(route(ReleaseGroupRouteMap::Get, $releaseGroupId))
            ->assertStatus(200)
            ->assertExactJson([
                'releaseGroup' => [
                    'releaseGroupId' => $releaseGroupId,
                    'title' => '観測された春',
                    'typeValue' => ReleaseGroupType::Album->value,
                    'description' => '1st アルバム',
                    'isDisplay' => true,
                ],
                'releases' => [
                    [
                        'releaseId' => $releaseId1,
                        'name' => '配信',
                        'releasedOn' => '2026-05-01',
                        'isDisplay' => true,
                        'formatValues' => [MediumFormat::Digital->value],
                    ],
                    [
                        'releaseId' => $releaseId2,
                        'name' => '初回限定盤',
                        'releasedOn' => '2026-06-01',
                        'isDisplay' => true,
                        'formatValues' => [MediumFormat::Cd->value, MediumFormat::Dvd->value],
                    ],
                ],
            ]);
    }

    #[Test]
    public function notFound(): void
    {
        $this->withAuth()
            ->getJson(route(ReleaseGroupRouteMap::Get, $this->generateUuid()))
            ->assertStatus(404);
    }

    #[Test]
    public function invalidId(): void
    {
        $this->withAuth()
            ->getJson('/api/admin/v1/release-groups/invalid-id')
            ->assertStatus(404);
    }
}
