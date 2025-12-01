<?php

declare(strict_types=1);

namespace Tests\Feature\Api\SongType;

use PHPUnit\Framework\Attributes\Test;
use SongType\Route\SongTypeRouteMap;
use Tests\TestCase;

class ListSongTypeTest extends TestCase
{
    #[Test]
    public function showList(): void
    {
        $this->get(route(SongTypeRouteMap::List))
            ->assertStatus(200)
            ->assertJson([
                'songTypes' => [
                    [
                        'songTypeName' => 'オリジナル曲',
                        'songTypeValue' => 1,
                    ],
                    [
                        'songTypeName' => 'カバー曲',
                        'songTypeValue' => 2,
                    ],
                    [
                        'songTypeName' => 'コラボ曲',
                        'songTypeValue' => 3,
                    ],
                    [
                        'songTypeName' => '系譜曲',
                        'songTypeValue' => 4,
                    ],
                    [
                        'songTypeName' => '派生曲',
                        'songTypeValue' => 5,
                    ],
                    [
                        'songTypeName' => '拡声曲',
                        'songTypeValue' => 6,
                    ],
                ],
            ]);
    }
}
