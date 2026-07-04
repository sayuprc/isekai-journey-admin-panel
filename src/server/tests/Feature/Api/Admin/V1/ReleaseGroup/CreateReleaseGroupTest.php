<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\ReleaseGroup;

use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseGroupType;
use Release\Route\ReleaseGroupRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class CreateReleaseGroupTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canCreate(): void
    {
        $this->withAuth()
            ->postJson(route(ReleaseGroupRouteMap::Create), [
                'title' => '観測された春',
                'typeValue' => ReleaseGroupType::Album->value,
                'description' => '1st アルバム',
                'isDisplay' => true,
            ])->assertStatus(200)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->has(
                        'releaseGroup',
                        static fn (AssertableJson $json) => $json
                            ->whereType('releaseGroupId', 'string')
                            ->where('title', '観測された春')
                            ->where('typeValue', ReleaseGroupType::Album->value)
                            ->where('description', '1st アルバム')
                            ->where('isDisplay', true),
                    ),
            );

        $this->assertDatabaseHas('release_groups', [
            'title' => '観測された春',
            'type' => ReleaseGroupType::Album->value,
            'description' => '1st アルバム',
            'is_display' => true,
        ]);
    }

    #[Test]
    public function forbidden(): void
    {
        $this->withGeneralAuth()
            ->postJson(route(ReleaseGroupRouteMap::Create), [
                'title' => '観測された春',
                'typeValue' => ReleaseGroupType::Album->value,
                'description' => '',
                'isDisplay' => true,
            ])->assertStatus(403);
    }
}
