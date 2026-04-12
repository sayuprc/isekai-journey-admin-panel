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

class DeleteSongTagTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $repository = $this->app->make(SongTagRepository::class);
        $repository->save(new SongTagDomain(
            SongTagId::reconstruct($uuid),
            SongTagName::reconstruct('ロック'),
            OrderNo::reconstruct(10),
        ));

        $this->withAuth()
            ->delete(route(SongTagRouteMap::Delete, $uuid))
            ->assertStatus(204);

        $this->assertNull($repository->find(SongTagId::reconstruct($uuid)));
    }

    #[Test]
    public function returnsValidationErrorWhenIdIsInvalid(): void
    {
        $this->withAuth()
            ->delete(route(SongTagRouteMap::Delete, 'invalid'))
            ->assertStatus(422);
    }
}
