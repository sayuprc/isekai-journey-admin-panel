<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Media;

use DateType\ImmutableDate;
use Media\Domain\Models\MediaFormat;
use Media\Domain\Models\MediaType;
use Media\Infrastructures\MediaRepository;
use Media\Route\MediaRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Song\Infrastructures\SongRepository;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class GetMediaTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function found(): void
    {
        $uuid = $this->generateUuid();
        $songId = $this->generateUuid();

        $repository = $this->app->make(MediaRepository::class);
        $repository->save(
            $this->createMedia(
                $uuid,
                '描き続けた君へ MV',
                'https://example.com/media',
                MediaType::Video,
                true,
                MediaFormat::Mv,
                new ImmutableDate('2024-03-01'),
            ),
        );
        $this->app->make(SongRepository::class)->save(
            $this->createSong(
                $songId,
                '描き続けた君へ',
                '説明',
                SongType::Original,
                true,
                10,
                [],
                [],
                [],
                [],
                [],
                [['mediaId' => $uuid, 'orderNo' => 2]],
            ),
        );

        $this->withAuth()
            ->getJson(route(MediaRouteMap::Get, $uuid))
            ->assertStatus(200)
            ->assertExactJson([
                'media' => [
                    'mediaId' => $uuid,
                    'title' => '描き続けた君へ MV',
                    'url' => 'https://example.com/media',
                    'publishedAt' => '2024-03-01',
                    'type' => [
                        'name' => MediaType::Video->getName(),
                        'value' => MediaType::Video->value,
                    ],
                    'format' => [
                        'name' => MediaFormat::Mv->getName(),
                        'value' => MediaFormat::Mv->value,
                    ],
                    'isDisplay' => true,
                ],
                'songs' => [[
                    'songId' => $songId,
                    'title' => '描き続けた君へ',
                    'songOrderNo' => 10,
                    'mediaOrderNo' => 2,
                ]],
            ]);
    }

    #[Test]
    public function notFound(): void
    {
        $this->withAuth()
            ->getJson(route(MediaRouteMap::Get, $this->generateUuid()))
            ->assertStatus(404);
    }
}
