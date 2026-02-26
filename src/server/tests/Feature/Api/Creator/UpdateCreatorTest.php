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

class UpdateCreatorTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;
    use WithAuth;

    #[Test]
    public function canUpdate(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(FileCreatorRepository::class, $this->createCreator($uuid, 'クリエイター')->toArray());

        $this->withAuth()
            ->putJson(route(CreatorRouteMap::Update, $uuid), [
                'name' => 'ヰ世界情緒',
            ])->assertStatus(200)
            ->assertExactJson([
                'creator' => [
                    'creatorId' => $uuid,
                    'name' => 'ヰ世界情緒',
                ],
            ]);
    }

    #[Test]
    public function updateFails(): void
    {
        $uuid = $this->generateUuid();
        $this->factory(FileCreatorRepository::class, $this->createCreator($uuid, 'クリエイター')->toArray());

        $this->withAuth()
            ->putJson(route(CreatorRouteMap::Update, $uuid), [
                'name' => '',
            ])->assertStatus(422)
            ->assertJson([
                'field' => 'name',
            ]);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $uuid = $this->generateUuid();
        $this->factory(FileCreatorRepository::class, $this->createCreator($uuid, 'クリエイター')->toArray());

        $this->withAuth()
            ->putJson(route(CreatorRouteMap::Update, $uuid), [])
            ->assertStatus(422);
    }
}
