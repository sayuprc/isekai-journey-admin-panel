<?php

declare(strict_types=1);

namespace App\Models\AdminUser;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property string               $admin_user_recovery_code_id 管理ユーザーリカバリーコードID
 * @property string               $admin_user_id               管理ユーザーID
 * @property string               $code                        リカバリーコード(ハッシュ)
 * @property int                  $status                      消費状態
 * @property CarbonImmutable|null $used_at                     使用日時
 * @property CarbonImmutable      $created_at                  作成日時
 * @property CarbonImmutable      $updated_at                  更新日時
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecoveryCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecoveryCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecoveryCode query()
 *
 * @mixin \Eloquent
 */
class RecoveryCode extends Model
{
    #[Override]
    protected $table = 'admin_user_recovery_codes';

    #[Override]
    protected $primaryKey = 'admin_user_recovery_code_id';

    #[Override]
    protected $keyType = 'string';

    #[Override]
    protected function casts()
    {
        return [
            'used_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
