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

class ListPerformerTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;
    use WithAuth;

    #[Test]
    public function showList(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(FilePerformerRepository::class, $this->createPerformer($uuid, 'ヰ世界情緒', 1)->toArray());

        $this->withAuth()
            ->get(route(PerformerRouteMap::List))
            ->assertStatus(200)
            ->assertJson([
                'performers' => [
                    [
                        'performerId' => $uuid,
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
            ->get(route(PerformerRouteMap::List))
            ->assertStatus(200)
            ->assertJson(['performers' => []]);
    }
}
