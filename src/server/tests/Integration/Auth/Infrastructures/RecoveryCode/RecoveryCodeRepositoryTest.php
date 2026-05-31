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

        $this->getInstance()->saveMany($codes);

        $this->assertSame(2, ModelsRecoveryCode::query()->count());
    }

    #[Test]
    public function saveManyDoesNothingForEmptyArray(): void
    {
        $this->getInstance()->saveMany([]);

        $this->assertSame(0, ModelsRecoveryCode::query()->count());
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

        $this->assertSame(0, ModelsRecoveryCode::query()->count());
    }

    #[Test]
    public function deleteByAdminUserIdRemovesOnlyTargetAdminUserCodes(): void
    {
        $otherAdminUser = $this->createAdminUser($this->generateUuid(), 'other@example.com', Role::General, [], new DateTimeImmutable());
        $this->app->make(AdminUserRepository::class)->register($otherAdminUser);

        $repository = $this->getInstance();
        $repository->saveMany([
            $this->buildCode($this->generateUuid(), 'hashed-1', ConsumptionStatus::Unused, null),
        ]);
        $repository->saveMany([
            $this->buildCode($this->generateUuid(), 'hashed-other', ConsumptionStatus::Unused, null, $otherAdminUser->adminUserId),
        ]);

        $repository->deleteByAdminUserId($this->adminUserId);

        $converter = $this->app->make(UuidConverterInterface::class);
        $remaining = ModelsRecoveryCode::query()
            ->where('admin_user_id', $converter->toBin($otherAdminUser->adminUserId->value))
            ->count();

        $this->assertSame(1, ModelsRecoveryCode::query()->count());
        $this->assertSame(1, $remaining);
    }

    private function buildCode(string $recoveryCodeId, string $hashedCode, ConsumptionStatus $status, ?DateTimeImmutable $usedAt, ?AdminUserId $adminUserId = null): RecoveryCode
    {
        return new RecoveryCode(
            RecoveryCodeId::reconstruct($recoveryCodeId),
            $adminUserId ?? $this->adminUserId,
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
