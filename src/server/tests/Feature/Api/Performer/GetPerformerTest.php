<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Performer;

use Performer\Infrastructures\PerformerRepository;
use Performer\Route\PerformerRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class GetPerformerTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function found(): void
    {
        $uuid = $this->generateUuid();

        $this->app->make(PerformerRepository::class)->save($this->createPerformer($uuid, 'ヰ世界情緒', 1));

        $this->withAuth()
            ->get(route(PerformerRouteMap::Get, $uuid))
            ->assertStatus(200)
            ->assertExactJson([
                'performer' => [
                    'performerId' => $uuid,
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
            ->get(route(PerformerRouteMap::Get, $uuid))
            ->assertStatus(404);
    }
}
