<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Media;

use Media\Domain\Models\MediaFormat;
use Media\Domain\Models\MediaType;
use Media\Infrastructures\MediaRepository;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Song\Infrastructures\SongRepository;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class DeleteMediaTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $repository = $this->app->make(MediaRepository::class);
        $media = $this->createMedia($uuid, '描き続けた君へ MV', 'https://example.com/media', MediaType::Video, true, MediaFormat::Mv);
        $repository->save($media);

        $this->withAuth()
            ->delete(route('media.delete', $uuid))
            ->assertStatus(204);

        $this->assertNull($repository->find($media->mediaId));
    }

    #[Test]
    public function cannotDeleteWhenUsedInSong(): void
    {
        $mediaId = $this->generateUuid();

        $repository = $this->app->make(MediaRepository::class);
        $media = $this->createMedia($mediaId, '描き続けた君へ MV', 'https://example.com/media', MediaType::Video, true, MediaFormat::Mv);
        $repository->save($media);

        $this->app->make(SongRepository::class)->save(
            $this->createSong(
                $this->generateUuid(),
                '曲名',
                '説明',
                null,
                SongType::Original,
                true,
                1,
                [],
                [],
                [],
                [
                    ['mediaId' => $mediaId, 'orderNo' => 1],
                ],
            ),
        );

        $this->withAuth()
            ->delete(route('media.delete', $mediaId))
            ->assertStatus(400)
            ->assertExactJson([
                'message' => 'このMediaは楽曲に使用されているため削除できません',
            ]);

        $this->assertNotNull($repository->find($media->mediaId));
    }

    #[Test]
    public function canDeleteEvenIfTargetDoesNotExist(): void
    {
        $this->withAuth()
            ->delete(route('media.delete', 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'))
            ->assertStatus(204);
    }
}
