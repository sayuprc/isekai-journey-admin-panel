<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Place;

use PHPUnit\Framework\Attributes\Test;
use Place\Domain\Models\PlaceKind;
use Place\Route\PlaceRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class GetPlaceTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function found(): void
    {
        $uuid = $this->generateUuid();

        $this->storePlaces($this->createPlace($uuid, 'テスト会場', PlaceKind::Physical));

        $this->withAuth()
            ->get(route(PlaceRouteMap::Get, $uuid))
            ->assertStatus(200)
            ->assertExactJson([
                'place' => [
                    'placeId' => $uuid,
                    'name' => 'テスト会場',
                    'kind' => [
                        'name' => PlaceKind::Physical->getName(),
                        'value' => PlaceKind::Physical->value,
                    ],
                ],
            ]);
    }

    #[Test]
    public function notFound(): void
    {
        $this->withAuth()
            ->get(route(PlaceRouteMap::Get, $this->generateUuid()))
            ->assertStatus(404);
    }
}
