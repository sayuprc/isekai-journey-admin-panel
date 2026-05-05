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

class GetMediaTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function found(): void
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
            ->getJson(route('media.get', $uuid))
            ->assertStatus(200)
            ->assertExactJson([
                'media' => [
                    'mediaId' => $uuid,
                    'title' => '描き続けた君へ MV',
                    'url' => 'https://example.com/media',
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
            ]);
    }

    #[Test]
    public function notFound(): void
    {
        $this->withAuth()
            ->getJson(route('media.get', $this->generateUuid()))
            ->assertStatus(404);
    }
}
