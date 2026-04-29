<?php

declare(strict_types=1);

namespace Tests\Feature\Api\SongTag;

use PHPUnit\Framework\Attributes\Test;
use SongTag\Route\SongTagRouteMap;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class ListSongTagTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function showList(): void
    {
        $uuid = $this->generateUuid();

        $this->storeSongTags($this->createSongTag($uuid, 'ライブ定番', 1));

        $this->withAuth()
            ->get(route(SongTagRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson([
                'tags' => [
                    [
                        'songTagId' => $uuid,
                        'name' => 'ライブ定番',
                        'orderNo' => 1,
                    ],
                ],
            ]);
    }
}
