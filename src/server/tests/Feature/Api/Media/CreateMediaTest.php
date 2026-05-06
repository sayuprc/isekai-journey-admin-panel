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

class CreateMediaTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canCreate(): void
    {
        $this->withAuth()
            ->postJson(route(MediaRouteMap::Create), [
                'title' => '描き続けた君へ MV',
                'url' => 'https://example.com/media',
                'typeValue' => MediaType::Video->value,
                'formatValue' => MediaFormat::Mv->value,
                'isDisplay' => true,
            ])->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereType('media.mediaId', 'string')
                ->where('media.title', '描き続けた君へ MV')
                ->where('media.url', 'https://example.com/media')
                ->where('media.type', [
                    'name' => MediaType::Video->getName(),
                    'value' => MediaType::Video->value,
                ])
                ->where('media.format', [
                    'name' => MediaFormat::Mv->getName(),
                    'value' => MediaFormat::Mv->value,
                ])
                ->where('media.isDisplay', true));
    }

    #[Test]
    public function duplicateUrlCannotBeCreated(): void
    {
        $repository = $this->app->make(MediaRepository::class);
        $repository->save(
            $this->createMedia(
                $this->generateUuid(),
                '既存メディア',
                'https://example.com/media',
                MediaType::Video,
                true,
                MediaFormat::Mv,
            ),
        );

        $this->withAuth()
            ->postJson(route(MediaRouteMap::Create), [
                'title' => '別タイトル',
                'url' => 'https://example.com/media',
                'typeValue' => MediaType::Video->value,
                'formatValue' => MediaFormat::StreamArchive->value,
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
