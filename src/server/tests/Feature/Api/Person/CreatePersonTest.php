<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Person;

use Illuminate\Testing\Fluent\AssertableJson;
use Person\Infrastructures\PersonRepository;
use Person\Route\PersonRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class CreatePersonTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canCreate(): void
    {
        $this->withAuth()
            ->postJson(route(PersonRouteMap::Create), [
                'name' => 'ヰ世界情緒',
            ])->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json
                    ->has(
                        'person',
                        fn (AssertableJson $json) => $json
                            ->whereType('personId', 'string')
                            ->where('name', 'ヰ世界情緒')
                            ->whereType('orderNo', 'integer'),
                    ),
            );
    }

    #[Test]
    public function createFailsWhenNameAlreadyExists(): void
    {
        $this->app->make(PersonRepository::class)->save(
            $this->createPerson($this->generateUuid(), 'ヰ世界情緒', 10),
        );

        $this->withAuth()
            ->postJson(route(PersonRouteMap::Create), [
                'name' => 'ヰ世界情緒',
            ])->assertStatus(400)
            ->assertExactJson([
                'message' => 'すでに使われている名前です "ヰ世界情緒"',
            ]);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->withAuth()
            ->postJson(route(PersonRouteMap::Create), [
                'name' => '',
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
