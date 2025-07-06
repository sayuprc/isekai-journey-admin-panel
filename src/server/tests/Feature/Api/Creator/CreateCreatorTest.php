<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Creator;

use Creator\Route\CreatorRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class CreateCreatorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function canCreate(): void
    {
        $this->postJson(route(CreatorRouteMap::Create), [
            'creatorName' => 'ヰ世界情緒',
        ])->assertStatus(200)
            ->assertJson([
                'creator' => [
                    'creatorName' => 'ヰ世界情緒',
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
