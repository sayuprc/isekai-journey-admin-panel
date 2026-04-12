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

class GetSongTagTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function found(): void
    {
        $uuid = $this->generateUuid();

        $this->app->make(SongTagRepository::class)->save(new SongTagDomain(
            SongTagId::reconstruct($uuid),
            SongTagName::reconstruct('ロック'),
            OrderNo::reconstruct(10),
        ));

        $this->withAuth()
            ->get(route(SongTagRouteMap::Get, $uuid))
            ->assertStatus(200)
            ->assertExactJson([
                'tag' => [
                    'songTagId' => $uuid,
                    'name' => 'ロック',
                    'orderNo' => 10,
                ],
            ]);
    }

    #[Test]
    public function notFound(): void
    {
        $uuid = $this->generateUuid();

        $this->withAuth()
            ->get(route(SongTagRouteMap::Get, $uuid))
            ->assertStatus(404);
    }
}
