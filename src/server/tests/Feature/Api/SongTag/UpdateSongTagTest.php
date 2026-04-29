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

class UpdateSongTagTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canUpdate(): void
    {
        $uuid = $this->generateUuid();

        $this->app->make(SongTagRepository::class)->save($this->createSongTag($uuid, '旧タグ', 1));

        $this->withAuth()
            ->putJson(route(SongTagRouteMap::Update, $uuid), [
                'name' => '派生曲',
                'orderNo' => 2,
            ])->assertStatus(200)
            ->assertExactJson([
                'tag' => [
                    'songTagId' => $uuid,
                    'name' => '派生曲',
                    'orderNo' => 2,
                ],
            ]);
    }

    #[Test]
    public function routeSongTagIdIsPrioritizedOverBodySongTagId(): void
    {
        $routeSongTagId = $this->generateUuid();
        $bodySongTagId = $this->generateUuid();

        $this->app->make(SongTagRepository::class)->save($this->createSongTag($routeSongTagId, '旧タグ', 1));

        $this->withAuth()
            ->putJson(route(SongTagRouteMap::Update, $routeSongTagId), [
                'songTagId' => $bodySongTagId,
                'name' => '派生曲',
                'orderNo' => 2,
            ])->assertStatus(200)
            ->assertExactJson([
                'tag' => [
                    'songTagId' => $routeSongTagId,
                    'name' => '派生曲',
                    'orderNo' => 2,
                ],
            ]);
    }

    #[Test]
    public function updateFailsWhenNameAlreadyExists(): void
    {
        $targetId = $this->generateUuid();
        $otherId = $this->generateUuid();

        $repository = $this->app->make(SongTagRepository::class);
        $repository->save($this->createSongTag($targetId, '旧タグ', 1));
        $repository->save($this->createSongTag($otherId, '派生曲', 2));

        $this->withAuth()
            ->putJson(route(SongTagRouteMap::Update, $targetId), [
                'name' => '派生曲',
                'orderNo' => 3,
            ])->assertStatus(400)
            ->assertExactJson([
                'message' => 'すでに使われている名前です "派生曲"',
            ]);
    }

    #[Test]
    public function updateFailsWhenSongTagDoesNotExist(): void
    {
        $uuid = $this->generateUuid();

        $this->withAuth()
            ->putJson(route(SongTagRouteMap::Update, $uuid), [
                'name' => '派生曲',
                'orderNo' => 2,
            ])->assertStatus(404);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $uuid = $this->generateUuid();

        $this->app->make(SongTagRepository::class)->save($this->createSongTag($uuid, '旧タグ', 1));

        $this->withAuth()
            ->putJson(route(SongTagRouteMap::Update, $uuid), [
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
