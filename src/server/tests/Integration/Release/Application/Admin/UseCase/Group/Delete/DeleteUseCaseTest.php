<?php

declare(strict_types=1);

namespace Tests\Integration\Release\Application\Admin\UseCase\Group\Delete;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Release\Application\Admin\UseCase\Group\Delete\DeleteInputData;
use Release\Application\Admin\UseCase\Group\Delete\DeleteUseCase;
use Release\Domain\Models\ReleaseGroupType;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Error\BusinessLogicError;
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
        $releaseGroupId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '削除対象', ReleaseGroupType::Album, true),
        );

        $result = $this->getInstance()->handle(new DeleteInputData($releaseGroupId));

        $this->assertTrue($result->isOk());
        $this->assertCount(0, DB::table('release_groups')->get()->all());

        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Delete, AuditTargetType::ReleaseGroup, $releaseGroupId);
        $snapshot = $log['snapshot'];
        $this->assertIsArray($snapshot);
        $this->assertSame('削除対象', $snapshot['title'] ?? null);
    }

    #[Test]
    public function cannotDeleteWhenReleasesExist(): void
    {
        $releaseGroupId = $this->generateUuid();
        $releaseId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '削除対象', ReleaseGroupType::Album, true),
        );
        $this->storeReleases(
            $this->createRelease($releaseId, $releaseGroupId, '通常盤', true),
        );

        $result = $this->getInstance()->handle(new DeleteInputData($releaseGroupId));

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(BusinessLogicError::class, $error);
        $this->assertSame('リリースが存在するため削除できません。', $error->message);
        $this->assertCount(1, DB::table('release_groups')->get()->all());
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
