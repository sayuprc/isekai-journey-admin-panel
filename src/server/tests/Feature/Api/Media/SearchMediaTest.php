<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Media;

use Media\Domain\Models\MediaType;
use Media\Infrastructures\MediaRepository;
use Media\Route\MediaRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\WithAuth;
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
        $repository->save($this->createMedia($this->generateUuid(), '描き続けた君へ MV', 'https://example.com/mv', MediaType::Video, true));
        $repository->save($this->createMedia($this->generateUuid(), '別の動画', 'https://example.com/other', MediaType::Article, true));

        $this->withAuth()
            ->getJson(route(MediaRouteMap::Search, ['title' => '描き続けた君へ']))
            ->assertStatus(200)
            ->assertJsonCount(1, 'media')
            ->assertJsonPath('media.0.title', '描き続けた君へ MV')
            ->assertJsonPath('maxPage', 1);
    }

    #[Test]
    public function canSearchWithoutTitle(): void
    {
        $repository = $this->app->make(MediaRepository::class);
        $repository->save($this->createMedia($this->generateUuid(), '描き続けた君へ MV', 'https://example.com/mv', MediaType::Video, true));

        $this->withAuth()
            ->getJson(route(MediaRouteMap::Search, ['per_page' => 25]))
            ->assertStatus(200)
            ->assertJsonCount(1, 'media')
            ->assertJsonPath('media.0.title', '描き続けた君へ MV')
            ->assertJsonPath('maxPage', 1);
    }
}
