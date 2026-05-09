<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Release;

use DateType\ImmutableDate;
use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseDistributionType;
use Release\Domain\Models\ReleaseType;
use Release\Infrastructures\ReleaseRepository;
use Release\Route\ReleaseRouteMap;
use Song\Domain\Models\SongType;
use Song\Infrastructures\SongRepository;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class GetReleaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function found(): void
    {
        $releaseId = $this->generateUuid();
        $songId = $this->generateUuid();

        $this->app->make(SongRepository::class)->save(
            $this->createSong(
                $songId,
                '観測者の歌',
                '説明',
                SongType::Original,
                true,
                1,
            ),
        );

        $this->app->make(ReleaseRepository::class)->save(
            $this->createRelease(
                $releaseId,
                '観測アルバム',
                ReleaseType::Album,
                ReleaseDistributionType::Digital,
                true,
                new ImmutableDate('2024-04-01'),
                'リリース説明',
                [['songId' => $songId, 'trackNo' => 1]],
            ),
        );

        $this->withAuth()
            ->getJson(route(ReleaseRouteMap::Get, $releaseId))
            ->assertStatus(200)
            ->assertExactJson([
                'release' => [
                    'releaseId' => $releaseId,
                    'title' => '観測アルバム',
                    'typeValue' => ReleaseType::Album->value,
                    'distributionTypeValue' => ReleaseDistributionType::Digital->value,
                    'releasedOn' => '2024-04-01',
                    'description' => 'リリース説明',
                    'isDisplay' => true,
                    'trackEntries' => [[
                        'songId' => $songId,
                        'trackNo' => 1,
                    ]],
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
}
