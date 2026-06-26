<?php

declare(strict_types=1);

namespace Tests\Integration\Release\Application\Admin\UseCase\Delete;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Release\Application\Admin\UseCase\Delete\DeleteInputData;
use Release\Application\Admin\UseCase\Delete\DeleteUseCase;
use Release\Domain\Models\ReleaseDistributionType;
use Release\Domain\Models\ReleaseType;
use Song\Domain\Models\SongType;
use Support\UseCase\AuditLog\AuditAction;
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
        $releaseId = $this->generateUuid();

        $this->storeReleases(
            $this->createRelease($releaseId, '削除対象', ReleaseType::Album, ReleaseDistributionType::Digital, true),
        );

        $result = $this->getInstance()->handle(new DeleteInputData($releaseId));

        $this->assertTrue($result->isOk());
        $this->assertCount(0, DB::table('releases')->get()->all());

        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Delete, AuditTargetType::Release, $releaseId);
        $snapshot = $log['snapshot'];
        $this->assertIsArray($snapshot);
        $this->assertSame('削除対象', $snapshot['title'] ?? null);
    }

    #[Test]
    public function canDeleteReleaseWithTrackEntries(): void
    {
        $songId = $this->generateUuid();
        $releaseId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId, 'テスト楽曲1', '説明', SongType::Original, true, 1),
        );
        $this->storeReleases(
            $this->createRelease(
                $releaseId,
                '削除対象',
                ReleaseType::Album,
                ReleaseDistributionType::Digital,
                true,
                trackEntries: [
                    ['songId' => $songId, 'trackNo' => 1],
                ],
            ),
        );

        $result = $this->getInstance()->handle(new DeleteInputData($releaseId));

        $this->assertTrue($result->isOk());
        $this->assertCount(0, DB::table('releases')->get()->all());
    }

    #[Test]
    public function canDeleteEvenIfTargetDoesNotExist(): void
    {
        $result = $this->getInstance()->handle(new DeleteInputData($this->generateUuid()));

        $this->assertTrue($result->isOk());
        $this->assertAuditLogCount(0);
    }

    private function getInstance(): DeleteUseCase
    {
        $this->privilegedContext();

        return $this->app->make(DeleteUseCase::class);
    }
}
