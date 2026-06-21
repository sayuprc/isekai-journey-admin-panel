<?php

declare(strict_types=1);

namespace Tests\Integration\Media\Application\Admin\UseCase;

use App\Models\Media\Media as ModelsMedia;
use Media\Application\Admin\UseCase\Update\UpdateInputData;
use Media\Application\Admin\UseCase\Update\UpdateUseCase;
use Media\Domain\Models\MediaFormat;
use Media\Domain\Models\MediaType;
use PHPUnit\Framework\Attributes\Test;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class UpdateUseCaseTest extends DatabaseTestCase
{
    use AssertsAuditLog;
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canUpdate(): void
    {
        $mediaId = $this->generateUuid();

        $this->storeMedia(
            $this->createMedia(
                $mediaId,
                'テストメディアMV',
                'https://example.com/media',
                MediaType::Video,
                true,
                MediaFormat::Mv,
            ),
        );

        $result = $this->getInstance()->handle(
            new UpdateInputData(
                $mediaId,
                'テストメディア配信アーカイブ',
                'https://example.com/archive',
                '2024-04-02',
                MediaType::SocialPost->value,
                MediaFormat::StreamArchive->value,
                false,
            ),
        );

        $this->assertTrue($result->isOk());

        $media = ModelsMedia::query()->first();
        $this->assertNotNull($media);
        $this->assertSame('テストメディア配信アーカイブ', $media->title);
        $this->assertSame('https://example.com/archive', $media->url);
        $this->assertSame('2024-04-02', $media->published_at?->format('Y-m-d'));
        $this->assertSame(MediaType::SocialPost->value, $media->type);
        $this->assertSame(MediaFormat::StreamArchive->value, $media->format);
        $this->assertFalse($media->is_display);

        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Update, AuditTargetType::Media, $mediaId);
        $this->assertSame('テストメディア配信アーカイブ', $log['snapshot']['title']);
        $this->assertSame(MediaType::SocialPost->value, $log['snapshot']['type']);
        $this->assertSame(MediaFormat::StreamArchive->value, $log['snapshot']['format']);
        $this->assertFalse($log['snapshot']['is_display']);
    }

    private function getInstance(): UpdateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(UpdateUseCase::class);
    }
}
