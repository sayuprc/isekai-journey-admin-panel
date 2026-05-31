<?php

declare(strict_types=1);

namespace Auth\Infrastructures\RecoveryCode;

use AdminUser\Domain\Models\AdminUserId;
use App\Models\AdminUser\RecoveryCode as ModelsRecoveryCode;
use Auth\Domain\Models\RecoveryCode\RecoveryCode;
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

    #[Override]
    public function deleteByAdminUserId(AdminUserId $adminUserId): void
    {
        ModelsRecoveryCode::query()
            ->where('admin_user_id', $this->converter->toBin($adminUserId->value))
            ->delete();
    }
}
