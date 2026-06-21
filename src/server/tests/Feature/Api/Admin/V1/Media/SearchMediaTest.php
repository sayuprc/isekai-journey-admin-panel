<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Media;

use DateType\ImmutableDate;
use Media\Domain\Models\MediaFormat;
use Media\Domain\Models\MediaType;
use Media\Infrastructures\MediaRepository;
use Media\Route\MediaRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class SearchMediaTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canSearchByTitle(): void
    {
        $repository = $this->app->make(MediaRepository::class);
        $repository->save($this->createMedia($this->generateUuid(), 'テストメディアMV', 'https://example.com/mv', MediaType::Video, true, MediaFormat::Mv, new ImmutableDate('2024-03-01')));
        $repository->save($this->createMedia($this->generateUuid(), '別の動画', 'https://example.com/other', MediaType::Article, true, MediaFormat::Other));

        $this->withAuth()
            ->getJson(route(MediaRouteMap::Search, ['title' => 'テストメディア']))
            ->assertStatus(200)
            ->assertJsonCount(1, 'media')
            ->assertJsonPath('media.0.title', 'テストメディアMV')
            ->assertJsonPath('media.0.publishedAt', '2024-03-01')
            ->assertJsonPath('maxPage', 1);
    }

    #[Test]
    public function canSearchWithoutTitle(): void
    {
        $repository = $this->app->make(MediaRepository::class);
        $repository->save($this->createMedia($this->generateUuid(), 'テストメディアMV', 'https://example.com/mv', MediaType::Video, true, MediaFormat::Mv));

        $this->withAuth()
            ->getJson(route(MediaRouteMap::Search, ['per_page' => 25]))
            ->assertStatus(200)
            ->assertJsonCount(1, 'media')
            ->assertJsonPath('media.0.title', 'テストメディアMV')
            ->assertJsonPath('maxPage', 1);
    }

    #[Test]
    public function canSearchByTypeFormatAndDisplay(): void
    {
        $repository = $this->app->make(MediaRepository::class);
        $repository->save($this->createMedia($this->generateUuid(), 'MV', 'https://example.com/mv', MediaType::Video, true, MediaFormat::Mv));
        $repository->save($this->createMedia($this->generateUuid(), '切り抜き', 'https://example.com/clip', MediaType::Video, true, MediaFormat::LiveClip));
        $repository->save($this->createMedia($this->generateUuid(), '記事', 'https://example.com/article', MediaType::Article, false, MediaFormat::Other));

        $this->withAuth()
            ->getJson(route(MediaRouteMap::Search, [
                'type' => MediaType::Video->value,
                'format' => MediaFormat::LiveClip->value,
                'is_display' => 'true',
            ]))
            ->assertStatus(200)
            ->assertJsonCount(1, 'media')
            ->assertJsonPath('media.0.title', '切り抜き')
            ->assertJsonPath('media.0.type.value', MediaType::Video->value)
            ->assertJsonPath('media.0.format.value', MediaFormat::LiveClip->value)
            ->assertJsonPath('media.0.isDisplay', true)
            ->assertJsonPath('maxPage', 1);
    }
}
