<?php

declare(strict_types=1);

namespace Tests\Feature\Api\SongTag;

use PHPUnit\Framework\Attributes\Test;
use SongTag\Route\SongTagRouteMap;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchSongTagTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function searchAll(): void
    {
        $uuid = $this->generateUuid();

        $this->storeSongTags($this->createSongTag($uuid, 'ライブ定番', 1));

        $this->withAuth()
            ->get(route(SongTagRouteMap::Search))
            ->assertStatus(200)
            ->assertExactJson([
                'tags' => [
                    [
                        'songTagId' => $uuid,
                        'name' => 'ライブ定番',
                        'orderNo' => 1,
                    ],
                ],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function searchByName(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storeSongTags(
            $this->createSongTag($uuid1, 'ライブ定番', 1),
            $this->createSongTag($uuid2, '周年', 2),
        );

        $this->withAuth()
            ->get(route(SongTagRouteMap::Search, ['name' => 'ライブ']))
            ->assertStatus(200)
            ->assertExactJson([
                'tags' => [
                    [
                        'songTagId' => $uuid1,
                        'name' => 'ライブ定番',
                        'orderNo' => 1,
                    ],
                ],
                'maxPage' => 1,
            ]);
    }
}
