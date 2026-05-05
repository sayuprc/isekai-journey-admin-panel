<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Media;

use Illuminate\Testing\Fluent\AssertableJson;
use Media\Domain\Models\MediaFormat;
use Media\Domain\Models\MediaType;
use Media\Infrastructures\MediaRepository;
use Media\Route\MediaRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\WithAuth;
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
                '描き続けた君へ MV',
                'https://example.com/media',
                MediaType::Video,
                true,
                MediaFormat::Mv,
            ),
        );

        $this->withAuth()
            ->putJson(route(MediaRouteMap::Update, $uuid), [
                'title' => '描き続けた君へ 配信アーカイブ',
                'url' => 'https://example.com/archive',
                'typeValue' => MediaType::SocialPost->value,
                'formatValue' => MediaFormat::StreamArchive->value,
                'isDisplay' => false,
            ])->assertStatus(200)
            ->assertExactJson([
                'media' => [
                    'mediaId' => $uuid,
                    'title' => '描き続けた君へ 配信アーカイブ',
                    'url' => 'https://example.com/archive',
                    'type' => [
                        'name' => MediaType::SocialPost->getName(),
                        'value' => MediaType::SocialPost->value,
                    ],
                    'format' => [
                        'name' => MediaFormat::StreamArchive->getName(),
                        'value' => MediaFormat::StreamArchive->value,
                    ],
                    'isDisplay' => false,
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
                '描き続けた君へ MV',
                'https://example.com/media',
                MediaType::Video,
                true,
                MediaFormat::Mv,
            ),
        );

        $this->withAuth()
            ->putJson(route(MediaRouteMap::Update, $routeMediaId), [
                'mediaId' => $bodyMediaId,
                'title' => '描き続けた君へ 配信アーカイブ',
                'url' => 'https://example.com/archive',
                'typeValue' => MediaType::Video->value,
                'formatValue' => MediaFormat::StreamArchive->value,
                'isDisplay' => true,
            ])->assertStatus(200)
            ->assertJsonPath('media.mediaId', $routeMediaId);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $uuid = $this->generateUuid();

        $repository = $this->app->make(MediaRepository::class);
        $repository->save(
            $this->createMedia(
                $uuid,
                '描き続けた君へ MV',
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
}
