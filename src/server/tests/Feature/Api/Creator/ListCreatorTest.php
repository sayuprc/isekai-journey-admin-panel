<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Creator;

use Creator\DebugInfrastructures\FileCreatorRepository;
use Creator\Route\CreatorRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class ListCreatorTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function showList(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(FileCreatorRepository::class, $uuid, $this->createCreator($uuid, 'ヰ世界情緒'));

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
