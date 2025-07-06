<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Creator;

use Creator\DebugInfrastructures\FileCreatorRepository;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Route\CreatorRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class UpdateCreatorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function canUpdate(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(
            FileCreatorRepository::class,
            $uuid,
            new Creator(new CreatorId($uuid), new CreatorName('クリエイター'))
        );

        $this->putJson(route(CreatorRouteMap::Update, $uuid), [
            'creatorName' => 'ヰ世界情緒',
        ])->assertStatus(200)
            ->assertJson([
                'creator' => [
                    'creatorId' => $uuid,
                    'creatorName' => 'ヰ世界情緒',
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
