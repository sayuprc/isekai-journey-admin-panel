<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Performer;

use Performer\DebugInfrastructures\FilePerformerRepository;
use Performer\Route\PerformerRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class UpdatePerformerTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function canUpdate(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(FilePerformerRepository::class, $uuid, $this->createPerformer($uuid, '共演者', 1));

        $this->putJson(route(PerformerRouteMap::Update, $uuid), [
            'performerName' => 'ヰ世界情緒',
            'orderNo' => 2,
        ])->assertStatus(200)
            ->assertJson([
                'performer' => [
                    'performerId' => $uuid,
                    'performerName' => 'ヰ世界情緒',
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
