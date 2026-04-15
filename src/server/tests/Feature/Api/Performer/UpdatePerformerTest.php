<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Performer;

use Performer\Infrastructures\PerformerRepository;
use Performer\Route\PerformerRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class UpdatePerformerTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canUpdate(): void
    {
        $uuid = $this->generateUuid();

        $this->app->make(PerformerRepository::class)->save($this->createPerformer($uuid, '共演者', 1));

        $this->withAuth()
            ->putJson(route(PerformerRouteMap::Update, $uuid), [
                'name' => 'ヰ世界情緒',
                'orderNo' => 2,
            ])->assertStatus(200)
            ->assertExactJson([
                'performer' => [
                    'performerId' => $uuid,
                    'name' => 'ヰ世界情緒',
                    'orderNo' => 2,
                ],
            ]);
    }

    #[Test]
    public function routePerformerIdIsPrioritizedOverBodyPerformerId(): void
    {
        $routePerformerId = $this->generateUuid();
        $bodyPerformerId = $this->generateUuid();

        $this->app->make(PerformerRepository::class)->save($this->createPerformer($routePerformerId, '共演者', 1));

        $this->withAuth()
            ->putJson(route(PerformerRouteMap::Update, $routePerformerId), [
                'performerId' => $bodyPerformerId,
                'name' => 'ヰ世界情緒',
                'orderNo' => 2,
            ])->assertStatus(200)
            ->assertExactJson([
                'performer' => [
                    'performerId' => $routePerformerId,
                    'name' => 'ヰ世界情緒',
                    'orderNo' => 2,
                ],
            ]);
    }

    #[Test]
    public function updateFails(): void
    {
        $this->markTestSkipped('TODO 実装する');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->markTestSkipped('TODO 実装する');
    }
}
