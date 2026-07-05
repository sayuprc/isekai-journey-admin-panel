<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Media;

use DateTimeImmutable;
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
        $repository->save($this->createMedia($this->generateUuid(), 'テストメディアMV', 'https://example.com/mv', MediaType::Mv, true, new DateTimeImmutable('2024-03-01 12:00:00')));
        $repository->save($this->createMedia($this->generateUuid(), '別の動画', 'https://example.com/other', MediaType::AudioVideo, true));

        $this->withAuth()
            ->getJson(route(MediaRouteMap::Search, ['title' => 'テストメディア']))
            ->assertStatus(200)
            ->assertJsonCount(1, 'media')
            ->assertJsonPath('media.0.title', 'テストメディアMV')
            ->assertJsonPath('media.0.publishedAt', '2024-03-01T12:00:00+09:00')
            ->assertJsonPath('maxPage', 1);
    }

    #[Test]
    public function canSearchWithoutTitle(): void
    {
        $repository = $this->app->make(MediaRepository::class);
        $repository->save($this->createMedia($this->generateUuid(), 'テストメディアMV', 'https://example.com/mv', MediaType::Mv, true));

        $this->withAuth()
            ->getJson(route(MediaRouteMap::Search, ['per_page' => 25]))
            ->assertStatus(200)
            ->assertJsonCount(1, 'media')
            ->assertJsonPath('media.0.title', 'テストメディアMV')
            ->assertJsonPath('maxPage', 1);
    }

    #[Test]
    public function canSearchByTypeAndDisplay(): void
    {
        $repository = $this->app->make(MediaRepository::class);
        $repository->save($this->createMedia($this->generateUuid(), 'MV', 'https://example.com/mv', MediaType::Mv, true));
        $repository->save($this->createMedia($this->generateUuid(), '非表示MV', 'https://example.com/hidden', MediaType::Mv, false));
        $repository->save($this->createMedia($this->generateUuid(), '配信', 'https://example.com/stream', MediaType::LiveStream, true));

        $this->withAuth()
            ->getJson(route(MediaRouteMap::Search, [
                'type' => MediaType::Mv->value,
                'is_display' => 'true',
            ]))
            ->assertStatus(200)
            ->assertJsonCount(1, 'media')
            ->assertJsonPath('media.0.title', 'MV')
            ->assertJsonPath('media.0.type.value', MediaType::Mv->value)
            ->assertJsonPath('media.0.isDisplay', true)
            ->assertJsonPath('maxPage', 1);
    }
}
