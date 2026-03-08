<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Performer;

use Performer\Route\PerformerRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchPerformerTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function searchAll(): void
    {
        $uuid = $this->generateUuid();

        $this->storePerformers($this->createPerformer($uuid, 'ヰ世界情緒', 1));

        $this->withAuth()
            ->get(route(PerformerRouteMap::Search))
            ->assertStatus(200)
            ->assertExactJson([
                'performers' => [
                    [
                        'performerId' => $uuid,
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

        $this->storePerformers(
            $this->createPerformer($uuid1, 'ヰ世界情緒', 1),
            $this->createPerformer($uuid2, '春猿火', 2),
        );

        $this->withAuth()
            ->get(route(PerformerRouteMap::Search, ['name' => 'ヰ世界情緒']))
            ->assertStatus(200)
            ->assertExactJson([
                'performers' => [
                    [
                        'performerId' => $uuid1,
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

        $this->storePerformers($this->createPerformer($uuid, 'ヰ世界情緒', 1));

        $this->withAuth()
            ->get(route(PerformerRouteMap::Search, ['name' => '存在しない名前']))
            ->assertStatus(200)
            ->assertExactJson([
                'performers' => [],
                'maxPage' => 0,
            ]);
    }
}
