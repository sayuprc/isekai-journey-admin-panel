<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Release;

use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseDistributionType;
use Release\Domain\Models\ReleaseId;
use Release\Domain\Models\ReleaseType;
use Release\Infrastructures\ReleaseRepository;
use Release\Route\ReleaseRouteMap;
use Song\Domain\Models\SongType;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class DeleteReleaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canDelete(): void
    {
        $releaseId = $this->generateUuid();
        $repository = $this->app->make(ReleaseRepository::class);

        $repository->save(
            $this->createRelease($releaseId, '削除対象', ReleaseType::Album, ReleaseDistributionType::Digital, true),
        );

        $this->withAuth()
            ->delete(route(ReleaseRouteMap::Delete, $releaseId))
            ->assertStatus(204);

        $this->assertNull($repository->find(ReleaseId::create($releaseId)->unwrap()));
    }

    #[Test]
    public function canDeleteReleaseWithTrackEntries(): void
    {
        $songId = $this->generateUuid();
        $releaseId = $this->generateUuid();
        $repository = $this->app->make(ReleaseRepository::class);

        $this->storeSongs(
            $this->createSong($songId, '一曲目', '説明', SongType::Original, true, 1),
        );

        $repository->save(
            $this->createRelease(
                $releaseId,
                '削除対象',
                ReleaseType::Album,
                ReleaseDistributionType::Digital,
                true,
                trackEntries: [
                    ['songId' => $songId, 'trackNo' => 1],
                ],
            ),
        );

        $this->withAuth()
            ->delete(route(ReleaseRouteMap::Delete, $releaseId))
            ->assertStatus(204);

        $this->assertNull($repository->find(ReleaseId::create($releaseId)->unwrap()));
    }

    #[Test]
    public function canDeleteEvenIfTargetDoesNotExist(): void
    {
        $this->withAuth()
            ->delete(route(ReleaseRouteMap::Delete, 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'))
            ->assertStatus(204);
    }

    #[Test]
    public function forbidden(): void
    {
        $releaseId = $this->generateUuid();

        $this->storeReleases(
            $this->createRelease($releaseId, '削除対象', ReleaseType::Album, ReleaseDistributionType::Digital, true),
        );

        $this->withGeneralAuth()
            ->delete(route(ReleaseRouteMap::Delete, $releaseId))
            ->assertStatus(403);
    }

    #[Test]
    public function invalidId(): void
    {
        $this->withAuth()
            ->delete('/api/admin/v1/releases/invalid-id')
            ->assertStatus(404);
    }
}
