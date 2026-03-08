<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Creator;

use Creator\Infrastructures\CreatorRepository;
use Creator\Route\CreatorRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class GetCreatorTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function found(): void
    {
        $uuid = $this->generateUuid();

        $this->app->make(CreatorRepository::class)->save($this->createCreator($uuid, 'ヰ世界情緒', 1));

        $this->withAuth()
            ->get(route(CreatorRouteMap::Get, $uuid))
            ->assertStatus(200)
            ->assertExactJson([
                'creator' => [
                    'creatorId' => $uuid,
                    'name' => 'ヰ世界情緒',
                    'orderNo' => 1,
                ],
            ]);
    }

    #[Test]
    public function notFound(): void
    {
        $uuid = $this->generateUuid();

        $this->withAuth()
            ->get(route(CreatorRouteMap::Get, $uuid))
            ->assertStatus(404);
    }
}
