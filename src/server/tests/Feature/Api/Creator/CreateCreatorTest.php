<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Creator;

use Creator\Route\CreatorRouteMap;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\WithAuth;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class CreateCreatorTest extends TestCase
{
    use FileRepositoryTransaction;
    use WithAuth;

    #[Test]
    public function canCreate(): void
    {
        $this->withAuth()
            ->postJson(route(CreatorRouteMap::Create), [
                'name' => 'ヰ世界情緒',
            ])->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json
                    ->has(
                        'creator',
                        fn (AssertableJson $json) => $json
                            ->whereType('creatorId', 'string')
                            ->where('name', 'ヰ世界情緒'),
                    ),
            );
    }

    #[Test]
    public function createFails(): void
    {
        $this->withAuth()
            ->postJson(route(CreatorRouteMap::Create), [
                'name' => '',
            ])->assertStatus(422);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->withAuth()
            ->postJson(route(CreatorRouteMap::Create), [])
            ->assertStatus(422);
    }
}
