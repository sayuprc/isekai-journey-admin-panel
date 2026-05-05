<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Media;

use Illuminate\Testing\Fluent\AssertableJson;
use Media\Domain\Models\MediaFormat;
use Media\Domain\Models\MediaType;
use Media\Route\MediaRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;

class CreateMediaTest extends DatabaseTestCase
{
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
}
