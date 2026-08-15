<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Place;

use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Place\Domain\Models\PlaceKind;
use Place\Route\PlaceRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class UpdatePlaceTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canUpdate(): void
    {
        $uuid = $this->generateUuid();

        $this->storePlaces($this->createPlace($uuid, '会場', PlaceKind::Physical));

        $this->withAuth()
            ->putJson(route(PlaceRouteMap::Update, $uuid), [
                'name' => 'テスト会場',
                'kindValue' => PlaceKind::Online->value,
            ])->assertStatus(200)
            ->assertExactJson([
                'place' => [
                    'placeId' => $uuid,
                    'name' => 'テスト会場',
                    'kind' => [
                        'name' => PlaceKind::Online->getName(),
                        'value' => PlaceKind::Online->value,
                    ],
                ],
            ]);
    }

    #[Test]
    public function routePlaceIdIsPrioritizedOverBodyPlaceId(): void
    {
        $routePlaceId = $this->generateUuid();
        $bodyPlaceId = $this->generateUuid();

        $this->storePlaces($this->createPlace($routePlaceId, '会場', PlaceKind::Physical));

        $this->withAuth()
            ->putJson(route(PlaceRouteMap::Update, $routePlaceId), [
                'placeId' => $bodyPlaceId,
                'name' => 'テスト会場',
                'kindValue' => PlaceKind::Online->value,
            ])->assertStatus(200)
            ->assertExactJson([
                'place' => [
                    'placeId' => $routePlaceId,
                    'name' => 'テスト会場',
                    'kind' => [
                        'name' => PlaceKind::Online->getName(),
                        'value' => PlaceKind::Online->value,
                    ],
                ],
            ]);
    }

    #[Test]
    public function updateFailsWhenPlaceDoesNotExist(): void
    {
        $placeId = $this->generateUuid();

        $this->withAuth()
            ->putJson(route(PlaceRouteMap::Update, $placeId), [
                'name' => 'テスト会場',
                'kindValue' => PlaceKind::Online->value,
            ])->assertStatus(404);
    }

    #[Test]
    public function updateFailsWhenNameAlreadyExists(): void
    {
        $targetId = $this->generateUuid();
        $otherId = $this->generateUuid();

        $this->storePlaces(
            $this->createPlace($targetId, '会場', PlaceKind::Physical),
            $this->createPlace($otherId, 'テスト会場', PlaceKind::Online),
        );

        $this->withAuth()
            ->putJson(route(PlaceRouteMap::Update, $targetId), [
                'name' => 'テスト会場',
                'kindValue' => PlaceKind::Physical->value,
            ])->assertStatus(400)
            ->assertExactJson([
                'code' => 'business_rule_violation',
                'message' => 'すでに使われている名前です "テスト会場"',
            ]);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $uuid = $this->generateUuid();

        $this->storePlaces($this->createPlace($uuid, '会場', PlaceKind::Physical));

        $this->withAuth()
            ->putJson(route(PlaceRouteMap::Update, $uuid), [
                'name' => '',
                'kindValue' => 0,
            ])->assertStatus(422)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->where('code', 'validation_failed')
                    ->whereType('message', 'string')
                    ->has('details', 2)
                    ->where('details.0.field', 'name')
                    ->whereType('details.0.message', 'string')
                    ->where('details.1.field', 'kindValue')
                    ->whereType('details.1.message', 'string'),
            );
    }
}
