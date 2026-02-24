<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Performer;

use Performer\Route\PerformerRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\WithAuth;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class CreatePerformerTest extends TestCase
{
    use FileRepositoryTransaction;
    use WithAuth;

    #[Test]
    public function canCreate(): void
    {
        $response = $this->withAuth()
            ->postJson(route(PerformerRouteMap::Create), [
                'name' => 'ヰ世界情緒',
            ]);

        $response->assertStatus(200);
        $performerId = $response->json('performer.performerId');

        $response->assertExactJson([
            'performer' => [
                'performerId' => $performerId,
                'name' => 'ヰ世界情緒',
                'orderNo' => 10,
            ],
        ]);
    }

    #[Test]
    public function createFails(): void
    {
        $this->markTestSkipped('実装する');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->markTestSkipped('実装する');
    }
}
