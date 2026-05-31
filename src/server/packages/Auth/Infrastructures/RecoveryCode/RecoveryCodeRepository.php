<?php

declare(strict_types=1);

namespace Auth\Infrastructures\RecoveryCode;

use AdminUser\Domain\Models\AdminUserId;
use App\Models\AdminUser\RecoveryCode as ModelsRecoveryCode;
use Auth\Domain\Models\RecoveryCode\ConsumptionStatus;
use Auth\Domain\Models\RecoveryCode\RecoveryCode;
use Auth\Domain\Models\RecoveryCode\RecoveryCodeId;
use Auth\Domain\Models\RecoveryCode\RecoveryCodeRepositoryInterface;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;

readonly class RecoveryCodeRepository implements RecoveryCodeRepositoryInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    /**
     * @param list<RecoveryCode> $codes
     */
    #[Override]
    public function saveMany(array $codes): void
    {
        if ($codes === []) {
            return;
        }

        $rows = array_map(function (RecoveryCode $code): array {
            $data = $code->toArray();

            return [
                'admin_user_recovery_code_id' => $this->converter->toBin($data['admin_user_recovery_code_id']),
                'admin_user_id' => $this->converter->toBin($data['admin_user_id']),
                'code' => $data['code'],
                'status' => $data['status'],
                'used_at' => $data['used_at'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $codes);

        ModelsRecoveryCode::query()->insert($rows);
    }

    /**
     * @return list<RecoveryCode>
     */
    #[Override]
    public function findUnusedByAdminUserIdForUpdate(AdminUserId $adminUserId): array
    {
        return array_values(ModelsRecoveryCode::query()
            ->where('admin_user_id', $this->converter->toBin($adminUserId->value))
            ->where('status', ConsumptionStatus::Unused->value)
            ->orderByDesc('created_at')
            ->lockForUpdate()
            ->get()
            ->map($this->hydrate(...))
            ->all());
    }

    #[Override]
    public function findUnusedByIdForUpdate(RecoveryCodeId $recoveryCodeId, AdminUserId $adminUserId): ?RecoveryCode
    {
        $model = ModelsRecoveryCode::query()
            ->where('admin_user_recovery_code_id', $this->converter->toBin($recoveryCodeId->value))
            ->where('admin_user_id', $this->converter->toBin($adminUserId->value))
            ->where('status', ConsumptionStatus::Unused->value)
            ->lockForUpdate()
            ->first();

        if (is_null($model)) {
            return null;
        }

        return $this->hydrate($model);
    }

    #[Override]
    public function save(RecoveryCode $code): RecoveryCode
    {
        $data = $code->toArray();

        ModelsRecoveryCode::query()->upsert(
            [
                [
                    'admin_user_recovery_code_id' => $this->converter->toBin($data['admin_user_recovery_code_id']),
                    'admin_user_id' => $this->converter->toBin($data['admin_user_id']),
                    'code' => $data['code'],
                    'status' => $data['status'],
                    'used_at' => $data['used_at'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ],
            ['admin_user_recovery_code_id'],
            ['status', 'used_at', 'updated_at'],
        );

        return $code;
    }

    #[Override]
    public function deleteByAdminUserId(AdminUserId $adminUserId): void
    {
        ModelsRecoveryCode::query()
            ->where('admin_user_id', $this->converter->toBin($adminUserId->value))
            ->delete();
    }

    private function hydrate(ModelsRecoveryCode $model): RecoveryCode
    {
        return RecoveryCode::reconstruct(
            $this->converter->toUuid($model->admin_user_recovery_code_id),
            $this->converter->toUuid($model->admin_user_id),
            $model->code,
            $model->status,
            $model->used_at?->toDateTimeImmutable(),
        );
    }
}
