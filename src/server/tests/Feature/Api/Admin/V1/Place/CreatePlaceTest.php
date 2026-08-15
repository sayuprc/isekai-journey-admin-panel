<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Place;

use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Place\Domain\Models\PlaceKind;
use Place\Infrastructures\PlaceRepository;
use Place\Route\PlaceRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class CreatePlaceTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canCreate(): void
    {
        $this->withAuth()
            ->postJson(route(PlaceRouteMap::Create), [
                'name' => 'テスト会場',
                'kindValue' => PlaceKind::Physical->value,
            ])->assertStatus(200)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->has(
                        'place',
                        static fn (AssertableJson $json) => $json
                            ->whereType('placeId', 'string')
                            ->where('name', 'テスト会場')
                            ->where('kind', [
                                'name' => PlaceKind::Physical->getName(),
                                'value' => PlaceKind::Physical->value,
                            ]),
                    ),
            );
    }

    #[Test]
    public function createFailsWhenNameAlreadyExists(): void
    {
        $this->app->make(PlaceRepository::class)->save(
            $this->createPlace($this->generateUuid(), 'テスト会場', PlaceKind::Physical),
        );

        $this->withAuth()
            ->postJson(route(PlaceRouteMap::Create), [
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
        $this->withAuth()
            ->postJson(route(PlaceRouteMap::Create), [
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
