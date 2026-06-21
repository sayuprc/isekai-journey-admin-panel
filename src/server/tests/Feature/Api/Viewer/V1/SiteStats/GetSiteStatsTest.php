<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Viewer\V1\SiteStats;

use PHPUnit\Framework\Attributes\Test;
use SiteStats\Route\ViewerSiteStatsRouteMap;
use Song\Domain\Models\SongType;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class GetSiteStatsTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function showPublicSongCount(): void
    {
        $this->storeSongs(
            $this->createSong(
                $this->generateUuid(),
                '公開楽曲 1',
                '公開テスト楽曲説明 1',
                SongType::Original,
                true,
                1,
            ),
            $this->createSong(
                $this->generateUuid(),
                '公開楽曲 2',
                '公開テスト楽曲説明 2',
                SongType::Cover,
                true,
                2,
            ),
            $this->createSong(
                $this->generateUuid(),
                '非公開楽曲',
                '非公開テスト楽曲説明',
                SongType::Cover,
                false,
                3,
            ),
        );

        $this->get(route(ViewerSiteStatsRouteMap::Get))
            ->assertStatus(200)
            ->assertExactJson([
                'songCount' => 2,
            ]);
    }
}
