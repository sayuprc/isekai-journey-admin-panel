<?php

declare(strict_types=1);

namespace Tests\Integration\Media\Application\UseCase;

use App\Models\Media\Media as ModelsMedia;
use Media\Application\UseCase\Delete\DeleteInputData;
use Media\Application\UseCase\Delete\DeleteUseCase;
use Media\Domain\Models\MediaFormat;
use Media\Domain\Models\MediaType;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Song\Domain\Models\SongType;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class DeleteUseCaseTest extends DatabaseTestCase
{
    use AssertsAuditLog;
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canDelete(): void
    {
        $mediaId = $this->generateUuid();

        $this->storeMedia(
            $this->createMedia(
                $mediaId,
                '描き続けた君へ MV',
                'https://example.com/media',
                MediaType::Video,
                true,
                MediaFormat::Mv,
            ),
        );

        $result = $this->getInstance()->handle(new DeleteInputData($mediaId));

        $this->assertTrue($result->isOk());
        $this->assertCount(0, ModelsMedia::query()->get()->all());

        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Delete, AuditTargetType::Media, $mediaId);
        $this->assertSame('描き続けた君へ MV', $log['snapshot']['title']);
    }

    #[Test]
    public function cannotDeleteWhenUsedInSong(): void
    {
        $mediaId = $this->generateUuid();

        $this->storeMedia(
            $this->createMedia(
                $mediaId,
                '描き続けた君へ MV',
                'https://example.com/media',
                MediaType::Video,
                true,
                MediaFormat::Mv,
            ),
        );
        $this->storeSongs($this->createSong(
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
        ));

        $result = $this->getInstance()->handle(new DeleteInputData($mediaId));

        $this->assertTrue($result->isErr());
        $this->assertSame('このメディアは楽曲に使用されているため削除できません', $result->unwrapErr()->message);
        $this->assertCount(1, ModelsMedia::query()->get()->all());
        $this->assertAuditLogCount(0);
    }

    #[Test]
    public function rollsBackDeleteWhenAuditLogRecorderThrows(): void
    {
        $mediaId = $this->generateUuid();

        $this->storeMedia(
            $this->createMedia(
                $mediaId,
                'ロールバック対象',
                'https://example.com/rollback',
                MediaType::Video,
                true,
                MediaFormat::Mv,
            ),
        );

        $this->privilegedContext();

        $recorder = Mockery::mock(AuditLogRecorderInterface::class);
        $recorder->shouldReceive('record')->andThrow(new RuntimeException('audit log failure'));
        $this->app->instance(AuditLogRecorderInterface::class, $recorder);

        try {
            $this->app->make(DeleteUseCase::class)->handle(new DeleteInputData($mediaId));
            $this->fail('RuntimeException が送出されるはず');
        } catch (RuntimeException $e) {
            $this->assertSame('audit log failure', $e->getMessage());
        }

        $this->assertCount(1, ModelsMedia::query()->where('title', 'ロールバック対象')->get());
        $this->assertAuditLogCount(0);
    }

    private function getInstance(): DeleteUseCase
    {
        $this->privilegedContext();

        return $this->app->make(DeleteUseCase::class);
    }
}
