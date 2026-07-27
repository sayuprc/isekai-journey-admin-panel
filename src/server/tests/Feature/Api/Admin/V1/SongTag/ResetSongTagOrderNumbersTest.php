<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\SongTag;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Tag\SongTagId;
use Song\Infrastructures\Tag\SongTagRepository;
use Song\Route\Tag\SongTagRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class ResetSongTagOrderNumbersTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function resetsOrderNumbersInStepsOfTen(): void
    {
        $repository = $this->app->make(SongTagRepository::class);

        $id1 = $this->generateUuid();
        $id2 = $this->generateUuid();

        $repository->save($this->createSongTag($id1, 'タグA', 5));
        $repository->save($this->createSongTag($id2, 'タグB', 15));

        $this->withAuth()
            ->postJson(route(SongTagRouteMap::ResetOrderNumbers))
            ->assertStatus(200)
            ->assertExactJson([
                'updatedCount' => 2,
            ]);

        $this->assertSame(10, $repository->find(SongTagId::reconstruct($id1))?->orderNo->value);
        $this->assertSame(20, $repository->find(SongTagId::reconstruct($id2))?->orderNo->value);
    }
}
