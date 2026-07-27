<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\ReleaseGroup;

use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseGroupId;
use Release\Domain\Models\ReleaseGroupType;
use Release\Infrastructures\ReleaseGroupRepository;
use Release\Route\ReleaseGroupRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class ResetReleaseGroupOrderNumbersTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function resetsOrderNumbersInStepsOfTen(): void
    {
        $repository = $this->app->make(ReleaseGroupRepository::class);

        $id1 = $this->generateUuid();
        $id2 = $this->generateUuid();

        $repository->save($this->createReleaseGroup($id1, 'グループA', ReleaseGroupType::Album, true, '説明', 2));
        $repository->save($this->createReleaseGroup($id2, 'グループB', ReleaseGroupType::Album, true, '説明', 8));

        $this->withAuth()
            ->postJson(route(ReleaseGroupRouteMap::ResetOrderNumbers))
            ->assertStatus(200)
            ->assertExactJson([
                'updatedCount' => 2,
            ]);

        $this->assertSame(10, $repository->find(ReleaseGroupId::reconstruct($id1))?->orderNo->value);
        $this->assertSame(20, $repository->find(ReleaseGroupId::reconstruct($id2))?->orderNo->value);
    }
}
