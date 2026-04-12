<?php

declare(strict_types=1);

namespace Tests\Feature\Api\SongTag;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Tag\SongTag as SongTagDomain;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagName;
use Song\Infrastructures\SongTagRepository;
use Song\Route\SongTagRouteMap;
use Support\Domain\ValueObjects\OrderNo;
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

        $this->app->make(SongTagRepository::class)->save(new SongTagDomain(
            SongTagId::reconstruct($uuid),
            SongTagName::reconstruct('ロック'),
            OrderNo::reconstruct(10),
        ));

        $this->withAuth()
            ->putJson(route(SongTagRouteMap::Update, $uuid), [
                'name' => 'バラード',
                'orderNo' => 20,
            ])->assertStatus(200)
            ->assertExactJson([
                'tag' => [
                    'songTagId' => $uuid,
                    'name' => 'バラード',
                    'orderNo' => 20,
                ],
            ]);
    }

    #[Test]
    public function notFound(): void
    {
        $uuid = $this->generateUuid();

        $this->withAuth()
            ->putJson(route(SongTagRouteMap::Update, $uuid), [
                'name' => 'バラード',
                'orderNo' => 20,
            ])->assertStatus(404);
    }

    #[Test]
    public function returnsErrorWhenDuplicateName(): void
    {
        $targetId = $this->generateUuid();
        $usedId = $this->generateUuid();

        $repository = $this->app->make(SongTagRepository::class);
        $repository->save(new SongTagDomain(
            SongTagId::reconstruct($targetId),
            SongTagName::reconstruct('ロック'),
            OrderNo::reconstruct(10),
        ));
        $repository->save(new SongTagDomain(
            SongTagId::reconstruct($usedId),
            SongTagName::reconstruct('ポップ'),
            OrderNo::reconstruct(20),
        ));

        $this->withAuth()
            ->putJson(route(SongTagRouteMap::Update, $targetId), [
                'name' => 'ポップ',
                'orderNo' => 30,
            ])->assertStatus(400)
            ->assertExactJson([
                'message' => 'すでに使われているタグ名です "ポップ"',
            ]);
    }

    #[Test]
    public function returnsValidationErrorWhenNameIsEmpty(): void
    {
        $uuid = $this->generateUuid();

        $this->app->make(SongTagRepository::class)->save(new SongTagDomain(
            SongTagId::reconstruct($uuid),
            SongTagName::reconstruct('ロック'),
            OrderNo::reconstruct(10),
        ));

        $this->withAuth()
            ->putJson(route(SongTagRouteMap::Update, $uuid), [
                'name' => '',
                'orderNo' => 20,
            ])->assertStatus(422);
    }
}
