<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Release;

use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Release\Route\ReleaseRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;

class CreateReleaseTest extends DatabaseTestCase
{
    use WithAuth;

    #[Test]
    public function canCreate(): void
    {
        $this->withAuth()
            ->postJson(route(ReleaseRouteMap::Create), [
                'title' => '観測された春',
                'typeValue' => 2,
                'distributionTypeValue' => 1,
                'releasedOn' => '2026-05-09',
                'description' => '説明',
                'isDisplay' => true,
            ])->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json
                    ->has(
                        'release',
                        fn (AssertableJson $json) => $json
                            ->whereType('releaseId', 'string')
                            ->where('title', '観測された春')
                            ->where('typeValue', 2)
                            ->where('distributionTypeValue', 1)
                            ->where('releasedOn', '2026-05-09')
                            ->where('description', '説明')
                            ->where('isDisplay', true)
                            ->where('trackEntries', []),
                    ),
            );
    }

    #[Test]
    public function createFailsWhenReleasedOnIsInvalid(): void
    {
        $this->withAuth()
            ->postJson(route(ReleaseRouteMap::Create), [
                'title' => '観測された春',
                'typeValue' => 2,
                'distributionTypeValue' => 1,
                'releasedOn' => 'invalid-date',
                'description' => '説明',
                'isDisplay' => true,
            ])->assertStatus(422)
            ->assertJson(
                fn (AssertableJson $json) => $json
                    ->has(
                        'errors',
                        1,
                        fn (AssertableJson $json) => $json
                            ->where('field', 'releasedOn')
                            ->where('message', 'The value does not match the expected format: date.'),
                    ),
            );
    }
}
