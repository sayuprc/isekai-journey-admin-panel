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
            ->assertJson([
                'creator' => [
                    'creatorId' => $uuid,
                    'name' => 'ヰ世界情緒',
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
