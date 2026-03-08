<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Creator;

use Creator\Route\CreatorRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchCreatorTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function searchAll(): void
    {
        $uuid = $this->generateUuid();

        $this->storeCreators($this->createCreator($uuid, 'ヰ世界情緒', 1));

        $this->withAuth()
            ->get(route(CreatorRouteMap::Search))
            ->assertStatus(200)
            ->assertExactJson([
                'creators' => [
                    [
                        'creatorId' => $uuid,
                        'name' => 'ヰ世界情緒',
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

        $this->storeCreators(
            $this->createCreator($uuid1, 'ヰ世界情緒', 1),
            $this->createCreator($uuid2, '香椎モイミ', 2),
        );

        $this->withAuth()
            ->get(route(CreatorRouteMap::Search, ['name' => 'ヰ世界情緒']))
            ->assertStatus(200)
            ->assertExactJson([
                'creators' => [
                    [
                        'creatorId' => $uuid1,
                        'name' => 'ヰ世界情緒',
                        'orderNo' => 1,
                    ],
                ],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function searchByNameNotFound(): void
    {
        $uuid = $this->generateUuid();

        $this->storeCreators($this->createCreator($uuid, 'ヰ世界情緒', 1));

        $this->withAuth()
            ->get(route(CreatorRouteMap::Search, ['name' => '存在しない名前']))
            ->assertStatus(200)
            ->assertExactJson([
                'creators' => [],
                'maxPage' => 0,
            ]);
    }
}
