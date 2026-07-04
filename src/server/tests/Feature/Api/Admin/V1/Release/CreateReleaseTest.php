<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Release;

use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\MediumFormat;
use Release\Domain\Models\ReleaseGroupType;
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
        $releaseGroupId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId, 'テスト楽曲1', '説明', SongType::Original, true, 1),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );

        $this->withAuth()
            ->postJson(route(ReleaseRouteMap::Create), [
                'releaseGroupId' => $releaseGroupId,
                'name' => '初回限定盤',
                'releasedOn' => '2026-05-09',
                'description' => '',
                'jacketArtUrl' => 'https://example.com/jacket.png',
                'isDisplay' => true,
                'media' => [
                    [
                        'position' => 1,
                        'formatValue' => MediumFormat::Cd->value,
                        'tracks' => [
                            ['songId' => $songId, 'trackNo' => 1],
                        ],
                    ],
                    [
                        'position' => 2,
                        'formatValue' => MediumFormat::Dvd->value,
                        'tracks' => [],
                    ],
                ],
            ])->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json
                    ->has(
                        'release',
                        fn (AssertableJson $json) => $json
                            ->whereType('releaseId', 'string')
                            ->where('releaseGroupId', $releaseGroupId)
                            ->where('name', '初回限定盤')
                            ->where('releasedOn', '2026-05-09')
                            ->where('description', '')
                            ->where('jacketArtUrl', 'https://example.com/jacket.png')
                            ->where('isDisplay', true)
                            ->where('media.0.position', 1)
                            ->where('media.0.formatValue', MediumFormat::Cd->value)
                            ->where('media.0.tracks.0.songId', $songId)
                            ->where('media.0.tracks.0.trackNo', 1)
                            ->where('media.1.position', 2)
                            ->where('media.1.formatValue', MediumFormat::Dvd->value)
                            ->where('media.1.tracks', []),
                    ),
            );

        $this->assertDatabaseCount('release_media', 2);
        $this->assertDatabaseCount('release_tracks', 1);
    }

    #[Test]
    public function createFailsWhenReleaseGroupDoesNotExist(): void
    {
        $this->withAuth()
            ->postJson(route(ReleaseRouteMap::Create), [
                'releaseGroupId' => $this->generateUuid(),
                'name' => '通常盤',
                'releasedOn' => '2026-05-09',
                'description' => '',
                'jacketArtUrl' => null,
                'isDisplay' => true,
                'media' => [],
            ])->assertStatus(400)
            ->assertJson(['message' => '指定されたリリースグループが存在しません。']);
    }

    #[Test]
    public function createFailsWhenReleasedOnIsInvalid(): void
    {
        $this->withAuth()
            ->postJson(route(ReleaseRouteMap::Create), [
                'releaseGroupId' => $this->generateUuid(),
                'name' => '通常盤',
                'releasedOn' => 'invalid-date',
                'description' => '説明',
                'jacketArtUrl' => null,
                'isDisplay' => true,
                'media' => [],
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
