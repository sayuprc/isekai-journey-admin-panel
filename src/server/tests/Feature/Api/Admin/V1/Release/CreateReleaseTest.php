<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Release;

use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Release\Route\ReleaseRouteMap;
use Song\Domain\Models\SongType;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class CreateReleaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canCreate(): void
    {
        $songId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId, '一曲目', '説明', SongType::Original, true, 1),
        );

        $this->withAuth()
            ->postJson(route(ReleaseRouteMap::Create), [
                'title' => '観測された春',
                'typeValue' => 2,
                'distributionTypeValue' => 1,
                'releasedOn' => '2026-05-09',
                'isDisplay' => true,
                'trackEntries' => [
                    ['songId' => $songId, 'trackNo' => 1],
                ],
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
                            ->where('description', '')
                            ->where('isDisplay', true)
                            ->where('trackEntries.0.songId', $songId)
                            ->where('trackEntries.0.trackNo', 1),
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
                'trackEntries' => [],
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
