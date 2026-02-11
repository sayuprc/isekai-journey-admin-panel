<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Performer;

use Performer\DebugInfrastructures\FilePerformerRepository;
use Performer\Route\PerformerRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\WithAuth;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class GetPerformerTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;
    use WithAuth;

    #[Test]
    public function found(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(FilePerformerRepository::class, $uuid, $this->createPerformer($uuid, 'ヰ世界情緒', 1));

        $this->withAuth()
            ->get(route(PerformerRouteMap::Get, $uuid))
            ->assertStatus(200)
            ->assertJson([
                'performer' => [
                    'performerId' => $uuid,
                    'performerName' => 'ヰ世界情緒',
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
