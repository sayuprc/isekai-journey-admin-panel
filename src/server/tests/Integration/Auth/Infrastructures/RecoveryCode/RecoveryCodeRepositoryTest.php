<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Infrastructures\RecoveryCode;

use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\Role;
use AdminUser\Infrastructures\AdminUserRepository;
use App\Models\AdminUser\RecoveryCode as ModelsRecoveryCode;
use Auth\Domain\Models\RecoveryCode\ConsumptionStatus;
use Auth\Domain\Models\RecoveryCode\HashedCodeValue;
use Auth\Domain\Models\RecoveryCode\RecoveryCode;
use Auth\Domain\Models\RecoveryCode\RecoveryCodeId;
use Auth\Infrastructures\RecoveryCode\RecoveryCodeRepository;
use Carbon\Carbon;
use DateTimeImmutable;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\Uuid\UuidConverterInterface;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class RecoveryCodeRepositoryTest extends DatabaseTestCase
{
    use EntityFactory;

    private AdminUserId $adminUserId;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-01-01 00:00:00');

        $adminUser = $this->createAdminUser($this->generateUuid(), 'user@example.com', Role::General, [], new DateTimeImmutable());
        $this->app->make(AdminUserRepository::class)->register($adminUser);

        $this->adminUserId = $adminUser->adminUserId;
    }

    #[Test]
    public function saveManyPersistsAllCodes(): void
    {
        $codes = [
            $this->buildCode($this->generateUuid(), 'hashed-1', ConsumptionStatus::Unused, null),
            $this->buildCode($this->generateUuid(), 'hashed-2', ConsumptionStatus::Unused, null),
        ];

        $repository = $this->getInstance();
        $repository->saveMany($codes);

        $found = $repository->findUnusedByAdminUserIdForUpdate($this->adminUserId);

        $this->assertCount(2, $found);
    }

    #[Test]
    public function findUnusedByAdminUserIdForUpdateReturnsOnlyUnusedCodes(): void
    {
        $repository = $this->getInstance();
        $repository->saveMany([
            $this->buildCode($this->generateUuid(), 'hashed-unused', ConsumptionStatus::Unused, null),
            $this->buildCode($this->generateUuid(), 'hashed-consumed', ConsumptionStatus::Consumed, now()->toDateTimeImmutable()),
        ]);

        $found = $repository->findUnusedByAdminUserIdForUpdate($this->adminUserId);

        $this->assertCount(1, $found);
        $this->assertSame('hashed-unused', $found[0]->code->value);
    }

    #[Test]
    public function findUnusedByAdminUserIdForUpdateReturnsEmptyWhenNoCodes(): void
    {
        $found = $this->getInstance()->findUnusedByAdminUserIdForUpdate($this->adminUserId);

        $this->assertSame([], $found);
    }

    #[Test]
    public function findUnusedByIdForUpdateReturnsMatchingUnusedCode(): void
    {
        $repository = $this->getInstance();
        $targetId = $this->generateUuid();
        $repository->saveMany([
            $this->buildCode($targetId, 'hashed-target', ConsumptionStatus::Unused, null),
            $this->buildCode($this->generateUuid(), 'hashed-other', ConsumptionStatus::Unused, null),
        ]);

        $found = $repository->findUnusedByIdForUpdate(RecoveryCodeId::reconstruct($targetId), $this->adminUserId);

        $this->assertNotNull($found);
        $this->assertSame('hashed-target', $found->code->value);
    }

    #[Test]
    public function findUnusedByIdForUpdateReturnsNullForConsumedCode(): void
    {
        $repository = $this->getInstance();
        $consumedId = $this->generateUuid();
        $repository->saveMany([
            $this->buildCode($consumedId, 'hashed-consumed', ConsumptionStatus::Consumed, now()->toDateTimeImmutable()),
        ]);

        $found = $repository->findUnusedByIdForUpdate(RecoveryCodeId::reconstruct($consumedId), $this->adminUserId);

        $this->assertNull($found);
    }

    #[Test]
    public function findUnusedByIdForUpdateReturnsNullForUnknownId(): void
    {
        $found = $this->getInstance()->findUnusedByIdForUpdate(
            RecoveryCodeId::reconstruct($this->generateUuid()),
            $this->adminUserId,
        );

        $this->assertNull($found);
    }

    #[Test]
    public function findUnusedByIdForUpdateReturnsNullForOtherAdminUser(): void
    {
        $repository = $this->getInstance();
        $targetId = $this->generateUuid();
        $repository->saveMany([
            $this->buildCode($targetId, 'hashed-target', ConsumptionStatus::Unused, null),
        ]);

        $otherAdminUser = $this->createAdminUser($this->generateUuid(), 'other@example.com', Role::General, [], new DateTimeImmutable());
        $this->app->make(AdminUserRepository::class)->register($otherAdminUser);

        $found = $repository->findUnusedByIdForUpdate(
            RecoveryCodeId::reconstruct($targetId),
            $otherAdminUser->adminUserId,
        );

        $this->assertNull($found);
    }

    #[Test]
    public function saveUpdatesStatusAndUsedAt(): void
    {
        $repository = $this->getInstance();
        $code = $this->buildCode($this->generateUuid(), 'hashed-1', ConsumptionStatus::Unused, null);
        $repository->saveMany([$code]);

        $usedAt = now()->toDateTimeImmutable();
        $repository->save($code->consume($usedAt));

        $this->assertSame([], $repository->findUnusedByAdminUserIdForUpdate($this->adminUserId));

        $converter = $this->app->make(UuidConverterInterface::class);
        $stored = ModelsRecoveryCode::query()
            ->where('admin_user_recovery_code_id', $converter->toBin($code->recoveryCodeId->value))
            ->first();

        $this->assertNotNull($stored);
        $this->assertSame(ConsumptionStatus::Consumed->value, $stored->status);
        $this->assertSame($usedAt->format('Y-m-d H:i:s'), $stored->used_at?->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function deleteByAdminUserIdRemovesAllCodes(): void
    {
        $repository = $this->getInstance();
        $repository->saveMany([
            $this->buildCode($this->generateUuid(), 'hashed-1', ConsumptionStatus::Unused, null),
            $this->buildCode($this->generateUuid(), 'hashed-2', ConsumptionStatus::Consumed, now()->toDateTimeImmutable()),
        ]);

        $repository->deleteByAdminUserId($this->adminUserId);

        $this->assertSame([], $repository->findUnusedByAdminUserIdForUpdate($this->adminUserId));
        $this->assertSame(0, ModelsRecoveryCode::query()->count());
    }

    private function buildCode(string $recoveryCodeId, string $hashedCode, ConsumptionStatus $status, ?DateTimeImmutable $usedAt): RecoveryCode
    {
        return new RecoveryCode(
            RecoveryCodeId::reconstruct($recoveryCodeId),
            $this->adminUserId,
            HashedCodeValue::reconstruct($hashedCode),
            $status,
            $usedAt,
        );
    }

    private function getInstance(): RecoveryCodeRepository
    {
        return $this->app->make(RecoveryCodeRepository::class);
    }
}
