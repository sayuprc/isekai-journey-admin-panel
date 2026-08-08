<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Release;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Release\Application\Admin\Storage\JacketArtStorageInterface;
use Release\Route\ReleaseRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;

class UploadJacketArtTest extends DatabaseTestCase
{
    use WithAuth;

    #[Test]
    public function canUpload(): void
    {
        $content = $this->pngContent();

        $storage = Mockery::mock(JacketArtStorageInterface::class);
        $storage
            ->shouldReceive('put')
            ->once()
            ->with(
                Mockery::pattern('/^release-jacket-art\/[0-9a-fA-F-]{36}\.png$/'),
                $content,
                'image/png',
            )
            ->andReturn('https://assets.example.com/release-jacket-art/test.png');

        $this->app->instance(JacketArtStorageInterface::class, $storage);

        $this->withAuth()
            ->call('POST', route(ReleaseRouteMap::UploadJacketArt), [], [], [], [
                'CONTENT_TYPE' => 'image/png',
            ], $content)
            ->assertStatus(200)
            ->assertJson([
                'jacketArtUrl' => 'https://assets.example.com/release-jacket-art/test.png',
            ]);
    }

    #[Test]
    public function rejectsUnsupportedContentType(): void
    {
        $storage = Mockery::mock(JacketArtStorageInterface::class);
        $storage->shouldNotReceive('put');

        $this->app->instance(JacketArtStorageInterface::class, $storage);

        $this->withAuth()
            ->call('POST', route(ReleaseRouteMap::UploadJacketArt), [], [], [], [
                'CONTENT_TYPE' => 'text/plain',
            ], 'not image')
            ->assertStatus(422)
            ->assertJsonStructure(['code', 'message', 'details']);
    }

    #[Test]
    public function rejectsInvalidImageContent(): void
    {
        $storage = Mockery::mock(JacketArtStorageInterface::class);
        $storage->shouldNotReceive('put');

        $this->app->instance(JacketArtStorageInterface::class, $storage);

        $this->withAuth()
            ->call('POST', route(ReleaseRouteMap::UploadJacketArt), [], [], [], [
                'CONTENT_TYPE' => 'image/png',
            ], 'not image')
            ->assertStatus(400)
            ->assertJsonPath('code', 'business_rule_violation')
            ->assertJsonPath('message', '画像ファイルの内容が不正です');
    }

    private function pngContent(): string
    {
        return base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=',
            true,
        ) ?: '';
    }
}
