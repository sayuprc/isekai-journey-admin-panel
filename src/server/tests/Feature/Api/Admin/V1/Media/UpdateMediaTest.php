<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Media;

use Illuminate\Testing\Fluent\AssertableJson;
use Media\Domain\Models\MediaFormat;
use Media\Domain\Models\MediaPlatform;
use Media\Domain\Models\MediaType;
use Media\Infrastructures\MediaRepository;
use Media\Route\MediaRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class UpdateMediaTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canUpdate(): void
    {
        $uuid = $this->generateUuid();

        $repository = $this->app->make(MediaRepository::class);
        $repository->save(
            $this->createMedia(
                $uuid,
                'テストメディアMV',
                'https://example.com/media',
                MediaType::Video,
                true,
                MediaFormat::Mv,
            ),
        );

        $this->withAuth()
            ->putJson(route(MediaRouteMap::Update, $uuid), [
                'title' => 'テストメディア配信アーカイブ',
                'url' => 'https://example.com/archive',
                'publishedAt' => '2024-04-02',
                'typeValue' => MediaType::SocialPost->value,
                'formatValue' => MediaFormat::StreamArchive->value,
                'isDisplay' => false,
            ])->assertStatus(200)
            ->assertExactJson([
                'media' => [
                    'mediaId' => $uuid,
                    'title' => 'テストメディア配信アーカイブ',
                    'url' => 'https://example.com/archive',
                    'publishedAt' => '2024-04-02',
                    'type' => [
                        'name' => MediaType::SocialPost->getName(),
                        'value' => MediaType::SocialPost->value,
                    ],
                    'format' => [
                        'name' => MediaFormat::StreamArchive->getName(),
                        'value' => MediaFormat::StreamArchive->value,
                    ],
                    'isDisplay' => false,
                    'platform' => [
                        'name' => MediaPlatform::Other->getName(),
                        'value' => MediaPlatform::Other->value,
                    ],
                ],
            ]);
    }

    #[Test]
    public function routeMediaIdIsPrioritizedOverBodyMediaId(): void
    {
        $routeMediaId = $this->generateUuid();
        $bodyMediaId = $this->generateUuid();

        $repository = $this->app->make(MediaRepository::class);
        $repository->save(
            $this->createMedia(
                $routeMediaId,
                'テストメディアMV',
                'https://example.com/media',
                MediaType::Video,
                true,
                MediaFormat::Mv,
            ),
        );

        $this->withAuth()
            ->putJson(route(MediaRouteMap::Update, $routeMediaId), [
                'mediaId' => $bodyMediaId,
                'title' => 'テストメディア配信アーカイブ',
                'url' => 'https://example.com/archive',
                'publishedAt' => '2024-04-02',
                'typeValue' => MediaType::Video->value,
                'formatValue' => MediaFormat::StreamArchive->value,
                'isDisplay' => true,
            ])->assertStatus(200)
            ->assertJsonPath('media.mediaId', $routeMediaId);
    }

    #[Test]
    public function updateFailsWhenMediaDoesNotExist(): void
    {
        $mediaId = $this->generateUuid();

        $this->withAuth()
            ->putJson(route(MediaRouteMap::Update, $mediaId), [
                'title' => 'テストメディア',
                'url' => 'https://example.com/media',
                'publishedAt' => '2024-04-02',
                'typeValue' => MediaType::Video->value,
                'formatValue' => MediaFormat::Mv->value,
                'isDisplay' => true,
            ])->assertStatus(404);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $uuid = $this->generateUuid();

        $repository = $this->app->make(MediaRepository::class);
        $repository->save(
            $this->createMedia(
                $uuid,
                'テストメディアMV',
                'https://example.com/media',
                MediaType::Video,
                true,
                MediaFormat::Mv,
            ),
        );

        $this->withAuth()
            ->putJson(route(MediaRouteMap::Update, $uuid), [
                'title' => '',
                'url' => '',
                'publishedAt' => '2024-04-02',
                'typeValue' => 0,
                'formatValue' => 0,
                'isDisplay' => true,
            ])->assertStatus(422)
            ->assertJson(
                fn (AssertableJson $json) => $json
                    ->has(
                        'errors',
                        1,
                        fn (AssertableJson $json) => $json
                            ->where('field', 'title')
                            ->whereType('message', 'string'),
                    ),
            );
    }

    #[Test]
    public function duplicateUrlCannotBeUpdated(): void
    {
        $targetMediaId = $this->generateUuid();
        $existingMediaId = $this->generateUuid();

        $repository = $this->app->make(MediaRepository::class);
        $repository->save(
            $this->createMedia(
                $targetMediaId,
                '更新対象メディア',
                'https://example.com/target',
                MediaType::Video,
                true,
                MediaFormat::Mv,
            ),
        );
        $repository->save(
            $this->createMedia(
                $existingMediaId,
                '既存メディア',
                'https://example.com/existing',
                MediaType::Video,
                true,
                MediaFormat::StreamArchive,
            ),
        );

        $this->withAuth()
            ->putJson(route(MediaRouteMap::Update, $targetMediaId), [
                'title' => '更新対象メディア',
                'url' => 'https://example.com/existing',
                'publishedAt' => '2024-04-02',
                'typeValue' => MediaType::Video->value,
                'formatValue' => MediaFormat::Mv->value,
                'isDisplay' => true,
            ])->assertStatus(422)
            ->assertJson(
                fn (AssertableJson $json) => $json
                    ->has(
                        'errors',
                        1,
                        fn (AssertableJson $json) => $json
                            ->where('field', 'url')
                            ->where('message', '同じURLのメディアが既に存在します'),
                    ),
            );
    }
}
