<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Creator;

use Creator\DebugInfrastructures\FileCreatorRepository;
use Creator\Route\CreatorRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\WithAuth;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class GetCreatorTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;
    use WithAuth;

    #[Test]
    public function found(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(FileCreatorRepository::class, $uuid, $this->createCreator($uuid, 'ヰ世界情緒'));

        $this->withAuth()
            ->get(route(CreatorRouteMap::Get, $uuid))
            ->assertStatus(200)
            ->assertJson([
                'creator' => [
                    'creatorId' => $uuid,
                    'creatorName' => 'ヰ世界情緒',
                ],
            ]);
    }

    #[Test]
    public function notFound(): void
    {
        $uuid = $this->generateUuid();

        $this->withAuth()
            ->get(route(CreatorRouteMap::Get, $uuid))
            ->assertStatus(404);
    }
}
