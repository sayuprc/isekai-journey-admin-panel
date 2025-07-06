<?php

declare(strict_types=1);

namespace Tests\Feature\Api\SongType;

use PHPUnit\Framework\Attributes\Test;
use SongType\Route\SongTypeRouteMap;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class CreateSongTypeTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function canCreate(): void
    {
        $this->postJson(route(SongTypeRouteMap::Create), [
            'songTypeName' => '楽曲種別',
            'orderNo' => 1,
        ])->assertStatus(200)
            ->assertJson([
                'songType' => [
                    'songTypeName' => '楽曲種別',
                    'orderNo' => 1,
                ],
            ]);
    }

    #[Test]
    public function createFails(): void
    {
        $this->markTestSkipped('実装する');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->markTestSkipped('実装する');
    }
}
