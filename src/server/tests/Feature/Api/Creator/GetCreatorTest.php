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

class GetCreatorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function found(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(
            FileCreatorRepository::class,
            $uuid,
            new Creator(new CreatorId($uuid), new CreatorName('ヰ世界情緒'))
        );

        $this->get(route(CreatorRouteMap::Get, $uuid))
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

        $this->get(route(CreatorRouteMap::Get, $uuid))
            ->assertStatus(404);
    }
}
