<?php

declare(strict_types=1);

namespace Tests\Feature\Api\SongTag;

use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Song\Infrastructures\Tag\SongTagRepository;
use Song\Route\Tag\SongTagRouteMap;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class CreateSongTagTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canCreate(): void
    {
        $this->withAuth()
            ->postJson(route(SongTagRouteMap::Create), [
                'name' => '派生曲',
            ])->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json
                    ->has(
                        'tag',
                        fn (AssertableJson $json) => $json
                            ->whereType('songTagId', 'string')
                            ->where('name', '派生曲')
                            ->where('orderNo', 10),
                    ),
            );
    }

    #[Test]
    public function createFailsWhenNameAlreadyExists(): void
    {
        $this->app->make(SongTagRepository::class)->save(
            $this->createSongTag($this->generateUuid(), '派生曲', 10),
        );

        $this->withAuth()
            ->postJson(route(SongTagRouteMap::Create), [
                'name' => '派生曲',
            ])->assertStatus(400)
            ->assertExactJson([
                'message' => 'すでに使われている名前です "派生曲"',
            ]);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->withAuth()
            ->postJson(route(SongTagRouteMap::Create), [
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
