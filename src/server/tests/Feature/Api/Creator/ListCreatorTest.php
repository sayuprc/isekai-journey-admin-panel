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

class ListCreatorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function showList(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(FileCreatorRepository::class, $uuid, new Creator(new CreatorId($uuid), new CreatorName('ヰ世界情緒')));

        $this->get(route(CreatorRouteMap::List))
            ->assertStatus(200)
            ->assertJson([
                'creators' => [
                    [
                        'creatorId' => $uuid,
                        'creatorName' => 'ヰ世界情緒',
                    ],
                ],
            ]);
    }

    #[Test]
    public function showEmptyList(): void
    {
        $this->get(route(CreatorRouteMap::List))
            ->assertStatus(200)
            ->assertJson(['creators' => []]);
    }
}
