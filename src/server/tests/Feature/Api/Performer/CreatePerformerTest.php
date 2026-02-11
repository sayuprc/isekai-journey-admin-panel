<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Performer;

use Performer\Route\PerformerRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class CreatePerformerTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function canCreate(): void
    {
        $this->postJson(route(PerformerRouteMap::Create), [
            'performerName' => 'ヰ世界情緒',
        ])->assertStatus(200)
            ->assertJson([
                'performer' => [
                    'performerName' => 'ヰ世界情緒',
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
