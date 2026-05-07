<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\SongTag;

use PHPUnit\Framework\Attributes\Test;
use Song\Route\Tag\SongTagRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchSongTagTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function returnUnauthorizedWhenUnauthenticated(): void
    {
        $this->get(route(SongTagRouteMap::Search))
            ->assertStatus(401);
    }

    #[Test]
    public function searchAll(): void
    {
        $songTagId = $this->generateUuid();

        $this->storeSongTags(
            $this->createSongTag($songTagId, '派生曲', 1),
        );

        $this->withAuth()
            ->get(route(SongTagRouteMap::Search))
            ->assertStatus(200)
            ->assertExactJson([
                'tags' => [
                    [
                        'songTagId' => $songTagId,
                        'name' => '派生曲',
                        'orderNo' => 1,
                    ],
                ],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function searchByName(): void
    {
        $matchedId = $this->generateUuid();
        $otherId = $this->generateUuid();

        $this->storeSongTags(
            $this->createSongTag($matchedId, '派生曲', 1),
            $this->createSongTag($otherId, 'ライブ披露曲', 2),
        );

        $this->withAuth()
            ->get(route(SongTagRouteMap::Search, ['name' => '派生']))
            ->assertStatus(200)
            ->assertExactJson([
                'tags' => [
                    [
                        'songTagId' => $matchedId,
                        'name' => '派生曲',
                        'orderNo' => 1,
                    ],
                ],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function searchByNameNotFound(): void
    {
        $songTagId = $this->generateUuid();

        $this->storeSongTags(
            $this->createSongTag($songTagId, '派生曲', 1),
        );

        $this->withAuth()
            ->get(route(SongTagRouteMap::Search, ['name' => '存在しない']))
            ->assertStatus(200)
            ->assertExactJson([
                'tags' => [],
                'maxPage' => 0,
            ]);
    }

    #[Test]
    public function searchWithSortAndPaging(): void
    {
        $ids = [];
        $tags = [];

        for ($i = 1; $i <= 26; $i++) {
            $id = $this->generateUuid();
            $name = sprintf('tag-%02d', $i);
            $ids[$name] = $id;
            $tags[] = $this->createSongTag($id, $name, $i);
        }

        $this->storeSongTags(...$tags);

        $this->withAuth()
            ->get(route(SongTagRouteMap::Search, [
                'sort' => 'name',
                'order' => 'desc',
                'page' => 2,
                'per_page' => 25,
            ]))
            ->assertStatus(200)
            ->assertExactJson([
                'tags' => [
                    [
                        'songTagId' => $ids['tag-01'],
                        'name' => 'tag-01',
                        'orderNo' => 1,
                    ],
                ],
                'maxPage' => 2,
            ]);
    }
}
