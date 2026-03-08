<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Creator;

use Creator\Infrastructures\CreatorRepository;
use Creator\Route\CreatorRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class UpdateCreatorTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canUpdate(): void
    {
        $uuid = $this->generateUuid();

        $this->app->make(CreatorRepository::class)->save($this->createCreator($uuid, 'クリエイター', 10));

        $this->withAuth()
            ->putJson(route(CreatorRouteMap::Update, $uuid), [
                'name' => 'ヰ世界情緒',
                'orderNo' => 20,
            ])->assertStatus(200)
            ->assertExactJson([
                'creator' => [
                    'creatorId' => $uuid,
                    'name' => 'ヰ世界情緒',
                    'orderNo' => 20,
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
