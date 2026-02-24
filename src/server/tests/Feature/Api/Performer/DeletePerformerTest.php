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

class DeletePerformerTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;
    use WithAuth;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(FilePerformerRepository::class, $this->createPerformer($uuid, '共演者', 1)->toArray());

        $this->withAuth()
            ->delete(route(PerformerRouteMap::Delete, $uuid))
            ->assertStatus(204);
    }

    #[Test]
    public function invalidId(): void
    {
        $this->withAuth()
            ->delete(route(PerformerRouteMap::Delete, 'invalid-uuid'))
            ->assertStatus(422);
    }
}
