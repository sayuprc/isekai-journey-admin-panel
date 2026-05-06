<?php

declare(strict_types=1);

namespace Tests\Integration\Media\Application\UseCase;

use App\Models\Media\Media as ModelsMedia;
use Media\Application\UseCase\Create\CreateInputData;
use Media\Application\UseCase\Create\CreateUseCase;
use Media\Domain\Models\MediaFormat;
use Media\Domain\Models\MediaType;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;

class CreateUseCaseTest extends DatabaseTestCase
{
    use AssertsAuditLog;

    #[Test]
    public function create(): void
    {
        $result = $this->getInstance()->handle(
            new CreateInputData(
                '描き続けた君へ MV',
                'https://example.com/media',
                '2024-03-01',
                MediaType::Video->value,
                MediaFormat::Mv->value,
                true,
            ),
        );

        $this->assertTrue($result->isOk());

        $media = ModelsMedia::query()->first();
        $this->assertNotNull($media);
        $this->assertSame('描き続けた君へ MV', $media->title);
        $this->assertSame('https://example.com/media', $media->url);
        $this->assertSame('2024-03-01', $media->published_at?->format('Y-m-d'));

        $mediaId = $this->toUuid($media->media_id);
        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Create, AuditTargetType::Media, $mediaId);
        $this->assertSame('描き続けた君へ MV', $log['snapshot']['title']);
        $this->assertSame(MediaType::Video->value, $log['snapshot']['type']);
        $this->assertSame(MediaFormat::Mv->value, $log['snapshot']['format']);
    }

    #[Test]
    public function rollsBackBusinessDataWhenAuditLogRecorderThrows(): void
    {
        $this->privilegedContext();

        $recorder = Mockery::mock(AuditLogRecorderInterface::class);
        $recorder->shouldReceive('record')->andThrow(new RuntimeException('audit log failure'));
        $this->app->instance(AuditLogRecorderInterface::class, $recorder);

        try {
            $this->app->make(CreateUseCase::class)->handle(
                new CreateInputData(
                    'ロールバック対象',
                    'https://example.com/rollback',
                    '2024-03-01',
                    MediaType::Video->value,
                    MediaFormat::Mv->value,
                    true,
                ),
            );
            $this->fail('RuntimeException が送出されるはず');
        } catch (RuntimeException $e) {
            $this->assertSame('audit log failure', $e->getMessage());
        }

        $this->assertCount(0, ModelsMedia::query()->where('title', 'ロールバック対象')->get());
        $this->assertAuditLogCount(0);
    }

    private function getInstance(): CreateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(CreateUseCase::class);
    }
}
