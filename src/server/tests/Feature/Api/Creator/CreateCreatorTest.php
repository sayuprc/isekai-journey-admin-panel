<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Creator;

use Creator\Route\CreatorRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\WithAuth;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class CreateCreatorTest extends TestCase
{
    use FileRepositoryTransaction;
    use WithAuth;

    #[Test]
    public function canCreate(): void
    {
        $response = $this->withAuth()
            ->postJson(route(CreatorRouteMap::Create), [
                'name' => 'ヰ世界情緒',
            ]);

        $response->assertStatus(200);
        $creatorId = $response->json('creator.creatorId');

        $response->assertExactJson([
            'creator' => [
                'creatorId' => $creatorId,
                'name' => 'ヰ世界情緒',
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
