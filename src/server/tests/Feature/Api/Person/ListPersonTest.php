<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Person;

use Person\Route\PersonRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class ListPersonTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function showList(): void
    {
        $uuid = $this->generateUuid();

        $this->storePersons($this->createPerson($uuid, 'ヰ世界情緒', 1));

        $this->withAuth()
            ->get(route(PersonRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson([
                'persons' => [
                    [
                        'personId' => $uuid,
                        'name' => 'ヰ世界情緒',
                        'orderNo' => 1,
                    ],
                ],
            ]);
    }

    #[Test]
    public function showEmptyList(): void
    {
        $this->withAuth()
            ->get(route(PersonRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson(['persons' => []]);
    }
}
