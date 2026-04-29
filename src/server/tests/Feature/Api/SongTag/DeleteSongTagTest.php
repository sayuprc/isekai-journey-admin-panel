<?php

declare(strict_types=1);

namespace Tests\Feature\Api\SongTag;

use PHPUnit\Framework\Attributes\Test;
use Song\Infrastructures\Tag\SongTagRepository;
use Song\Route\Tag\SongTagRouteMap;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class DeleteSongTagTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $repository = $this->app->make(SongTagRepository::class);
        $repository->save($this->createSongTag($uuid, '派生曲', 1));

        $this->withAuth()
            ->delete(route(SongTagRouteMap::Delete, $uuid))
            ->assertStatus(204);

        $this->assertNull($repository->find($this->createSongTag($uuid, '派生曲', 1)->songTagId));
    }

    #[Test]
    public function canDeleteEvenIfTargetDoesNotExist(): void
    {
        $this->withAuth()
            ->delete(route(SongTagRouteMap::Delete, 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'))
            ->assertStatus(204);
    }

    #[Test]
    public function requiresAuthentication(): void
    {
        $this->delete(route(SongTagRouteMap::Delete, 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'))
            ->assertStatus(401);
    }
}
