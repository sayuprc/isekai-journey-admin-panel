<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Release;

use App\Models\Release\TrackEntry as ModelsTrackEntry;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseDistributionType;
use Release\Domain\Models\ReleaseType;
use Release\Route\ReleaseRouteMap;
use Song\Domain\Models\SongType;
use Support\Contracts\Uuid\UuidConverterInterface;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class UpdateReleaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canUpdate(): void
    {
        $songId1 = $this->generateUuid();
        $songId2 = $this->generateUuid();
        $songId3 = $this->generateUuid();
        $releaseId = $this->generateUuid();

        $converter = $this->app->make(UuidConverterInterface::class);

        $this->storeSongs(
            $this->createSong($songId1, 'テスト楽曲1', '説明', SongType::Original, true, 10),
            $this->createSong($songId2, 'テスト楽曲2', '説明', SongType::Original, true, 20),
            $this->createSong($songId3, 'テスト楽曲3', '説明', SongType::Cover, false, 30),
        );
        $this->storeReleases(
            $this->createRelease(
                $releaseId,
                '旧タイトル',
                ReleaseType::Album,
                ReleaseDistributionType::Digital,
                true,
                trackEntries: [
                    ['songId' => $songId1, 'trackNo' => 1],
                    ['songId' => $songId2, 'trackNo' => 2],
                ],
            ),
        );

        $this->withAuth()
            ->putJson(route(ReleaseRouteMap::Update, $releaseId), [
                'title' => '新タイトル',
                'typeValue' => ReleaseType::Single->value,
                'distributionTypeValue' => ReleaseDistributionType::Physical->value,
                'releasedOn' => '2026-05-09',
                'description' => '更新後の説明',
                'isDisplay' => false,
                'trackEntries' => [
                    ['songId' => $songId3, 'trackNo' => 1],
                    ['songId' => $songId1, 'trackNo' => 2],
                ],
            ])->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json
                    ->has(
                        'release',
                        fn (AssertableJson $json) => $json
                            ->where('releaseId', $releaseId)
                            ->where('title', '新タイトル')
                            ->where('typeValue', ReleaseType::Single->value)
                            ->where('distributionTypeValue', ReleaseDistributionType::Physical->value)
                            ->where('releasedOn', '2026-05-09')
                            ->where('description', '更新後の説明')
                            ->where('isDisplay', false)
                            ->where('trackEntries.0.songId', $songId3)
                            ->where('trackEntries.0.trackNo', 1)
                            ->where('trackEntries.1.songId', $songId1)
                            ->where('trackEntries.1.trackNo', 2),
                    ),
            );

        $entries = ModelsTrackEntry::query()
            ->where('release_id', $converter->toBin($releaseId))
            ->orderBy('track_no')
            ->get()
            ->all();

        $this->assertCount(2, $entries);
        $this->assertSame($songId3, $this->toUuid($entries[0]->song_id));
        $this->assertSame($songId1, $this->toUuid($entries[1]->song_id));
    }

    #[Test]
    public function notFound(): void
    {
        $this->withAuth()
            ->putJson(route(ReleaseRouteMap::Update, $this->generateUuid()), [
                'title' => '新タイトル',
                'typeValue' => ReleaseType::Album->value,
                'distributionTypeValue' => ReleaseDistributionType::Digital->value,
                'releasedOn' => '2026-05-09',
                'description' => '説明',
                'isDisplay' => true,
                'trackEntries' => [],
            ])->assertStatus(404);
    }

    #[Test]
    public function invalidId(): void
    {
        $this->withAuth()
            ->putJson('/api/admin/v1/releases/invalid-id', [
                'title' => '新タイトル',
                'typeValue' => ReleaseType::Album->value,
                'distributionTypeValue' => ReleaseDistributionType::Digital->value,
                'releasedOn' => '2026-05-09',
                'description' => '説明',
                'isDisplay' => true,
                'trackEntries' => [],
            ])->assertStatus(404);
    }

    #[Test]
    public function forbidden(): void
    {
        $releaseId = $this->generateUuid();

        $this->storeReleases(
            $this->createRelease(
                $releaseId,
                '旧タイトル',
                ReleaseType::Album,
                ReleaseDistributionType::Digital,
                true,
            ),
        );

        $this->withGeneralAuth()
            ->putJson(route(ReleaseRouteMap::Update, $releaseId), [
                'title' => '新タイトル',
                'typeValue' => ReleaseType::Album->value,
                'distributionTypeValue' => ReleaseDistributionType::Digital->value,
                'releasedOn' => '2026-05-09',
                'description' => '説明',
                'isDisplay' => true,
                'trackEntries' => [],
            ])->assertStatus(403);
    }

    #[Test]
    public function updateFailsWhenTrackEntriesAreDuplicated(): void
    {
        $songId = $this->generateUuid();
        $releaseId = $this->generateUuid();

        $this->storeSongs($this->createSong($songId, 'テスト楽曲1', '説明', SongType::Original, true, 10));
        $this->storeReleases(
            $this->createRelease(
                $releaseId,
                '旧タイトル',
                ReleaseType::Album,
                ReleaseDistributionType::Digital,
                true,
            ),
        );

        $this->withAuth()
            ->putJson(route(ReleaseRouteMap::Update, $releaseId), [
                'title' => '新タイトル',
                'typeValue' => ReleaseType::Album->value,
                'distributionTypeValue' => ReleaseDistributionType::Digital->value,
                'releasedOn' => '2026-05-09',
                'description' => '説明',
                'isDisplay' => true,
                'trackEntries' => [
                    ['songId' => $songId, 'trackNo' => 1],
                    ['songId' => $songId, 'trackNo' => 2],
                ],
            ])->assertStatus(422)
            ->assertJson(
                fn (AssertableJson $json) => $json
                    ->has(
                        'errors',
                        1,
                        fn (AssertableJson $json) => $json
                            ->where('field', 'trackEntries')
                            ->where('message', '同じ楽曲を複数指定することはできません。'),
                    ),
            );
    }
}
