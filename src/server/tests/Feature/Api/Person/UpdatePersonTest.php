<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Person;

use Illuminate\Testing\Fluent\AssertableJson;
use Person\Route\PersonRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class UpdatePersonTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canUpdate(): void
    {
        $uuid = $this->generateUuid();

        $this->storePersons($this->createPerson($uuid, '人物', 10));

        $this->withAuth()
            ->putJson(route(PersonRouteMap::Update, $uuid), [
                'name' => 'ヰ世界情緒',
                'orderNo' => 20,
            ])->assertStatus(200)
            ->assertExactJson([
                'person' => [
                    'personId' => $uuid,
                    'name' => 'ヰ世界情緒',
                    'orderNo' => 20,
                ],
            ]);
    }

    #[Test]
    public function routePersonIdIsPrioritizedOverBodyPersonId(): void
    {
        $routePersonId = $this->generateUuid();
        $bodyPersonId = $this->generateUuid();

        $this->storePersons($this->createPerson($routePersonId, '人物', 10));

        $this->withAuth()
            ->putJson(route(PersonRouteMap::Update, $routePersonId), [
                'personId' => $bodyPersonId,
                'name' => 'ヰ世界情緒',
                'orderNo' => 20,
            ])->assertStatus(200)
            ->assertExactJson([
                'person' => [
                    'personId' => $routePersonId,
                    'name' => 'ヰ世界情緒',
                    'orderNo' => 20,
                ],
            ]);
    }

    #[Test]
    public function updateFailsWhenNameAlreadyExists(): void
    {
        $targetId = $this->generateUuid();
        $otherId = $this->generateUuid();

        $this->storePersons(
            $this->createPerson($targetId, '人物', 10),
            $this->createPerson($otherId, 'ヰ世界情緒', 20),
        );

        $this->withAuth()
            ->putJson(route(PersonRouteMap::Update, $targetId), [
                'name' => 'ヰ世界情緒',
                'orderNo' => 30,
            ])->assertStatus(400)
            ->assertExactJson([
                'message' => 'すでに使われている名前です "ヰ世界情緒"',
            ]);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $uuid = $this->generateUuid();

        $this->storePersons($this->createPerson($uuid, '人物', 10));

        $this->withAuth()
            ->putJson(route(PersonRouteMap::Update, $uuid), [
                'name' => '',
                'orderNo' => 0,
            ])->assertStatus(422)
            ->assertJson(
                fn (AssertableJson $json) => $json
                    ->has(
                        'errors',
                        1,
                        fn (AssertableJson $json) => $json
                            ->where('field', 'name')
                            ->whereType('message', 'string'),
                    ),
            );
    }
}
