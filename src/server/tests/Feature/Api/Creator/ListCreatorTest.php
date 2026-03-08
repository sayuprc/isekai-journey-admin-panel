<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Creator;

use Creator\Infrastructures\CreatorRepository;
use Creator\Route\CreatorRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class ListCreatorTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function showList(): void
    {
        $uuid = $this->generateUuid();

        $this->app->make(CreatorRepository::class)->save($this->createCreator($uuid, 'ヰ世界情緒', 1));

        $this->withAuth()
            ->get(route(CreatorRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson([
                'creators' => [
                    [
                        'creatorId' => $uuid,
                        'name' => 'ヰ世界情緒',
                        'orderNo' => 1,
                    ],
                ],
            ]);
    }

    #[Test]
    public function showEmptyList(): void
    {
        $this->withAuth()
            ->get(route(CreatorRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson(['creators' => []]);
    }
}
