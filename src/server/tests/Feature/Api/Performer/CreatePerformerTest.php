<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Performer;

use Illuminate\Testing\Fluent\AssertableJson;
use Performer\Route\PerformerRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\WithAuth;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class CreatePerformerTest extends TestCase
{
    use FileRepositoryTransaction;
    use WithAuth;

    #[Test]
    public function canCreate(): void
    {
        $this->withAuth()
            ->postJson(route(PerformerRouteMap::Create), [
                'name' => 'ヰ世界情緒',
            ])->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json
                    ->has(
                        'performer',
                        fn (AssertableJson $json) => $json
                            ->whereType('performerId', 'string')
                            ->where('name', 'ヰ世界情緒')
                            ->where('orderNo', 10),
                    ),
            );
    }

    #[Test]
    public function createFails(): void
    {
        $this->withAuth()
            ->postJson(route(PerformerRouteMap::Create), [
                'name' => '',
            ])->assertStatus(422)
            ->assertJson([
                'field' => 'name',
            ]);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->withAuth()
            ->postJson(route(PerformerRouteMap::Create), [])
            ->assertStatus(422);
    }
}
