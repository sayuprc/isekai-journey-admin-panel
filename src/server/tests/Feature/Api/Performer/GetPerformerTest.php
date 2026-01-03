<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Performer;

use Performer\DebugInfrastructures\FilePerformerRepository;
use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerName;
use Performer\Route\PerformerRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class GetPerformerTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function found(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(
            FilePerformerRepository::class,
            $uuid,
            new Performer(new PerformerId($uuid), new PerformerName('ヰ世界情緒'), new OrderNo(1)),
        );

        $this->get(route(PerformerRouteMap::Get, $uuid))
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

        $this->get(route(PerformerRouteMap::Get, $uuid))
            ->assertStatus(404);
    }
}
