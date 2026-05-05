<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Media;

use Media\Domain\Models\MediaFormat;
use Media\Domain\Models\MediaType;
use Media\Infrastructures\MediaRepository;
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
        $repository->save($this->createMedia($this->generateUuid(), '描き続けた君へ MV', 'https://example.com/mv', MediaType::Video, true, MediaFormat::Mv));
        $repository->save($this->createMedia($this->generateUuid(), '別の動画', 'https://example.com/other', MediaType::Article, true, MediaFormat::Other));

        $this->withAuth()
            ->getJson(route('media.search', ['title' => '描き続けた君へ']))
            ->assertStatus(200)
            ->assertJsonCount(1, 'media')
            ->assertJsonPath('media.0.title', '描き続けた君へ MV')
            ->assertJsonPath('maxPage', 1);
    }

    #[Test]
    public function canSearchWithoutTitle(): void
    {
        $repository = $this->app->make(MediaRepository::class);
        $repository->save($this->createMedia($this->generateUuid(), '描き続けた君へ MV', 'https://example.com/mv', MediaType::Video, true, MediaFormat::Mv));

        $this->withAuth()
            ->getJson(route('media.search', ['per_page' => 25]))
            ->assertStatus(200)
            ->assertJsonCount(1, 'media')
            ->assertJsonPath('media.0.title', '描き続けた君へ MV')
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
            ->getJson(route('media.search', [
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
