<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property string               $audit_log_id  監査ログID
 * @property string               $admin_user_id 実行者の管理ユーザーID
 * @property string               $action        操作種別
 * @property string               $target_type   対象種別
 * @property string               $target_id     対象ID
 * @property array<string, mixed> $snapshot      変更後スナップショット
 * @property CarbonImmutable      $created_at    作成日時
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog query()
 *
 * @mixin \Eloquent
 */
class AuditLog extends Model
{
    #[Override]
    protected $table = 'audit_logs';

    #[Override]
    protected $primaryKey = 'audit_log_id';

    #[Override]
    protected $keyType = 'string';

    #[Override]
    public $timestamps = false;

    #[Override]
    protected $guarded = [];

    #[Override]
    protected function casts()
    {
        return [
            'snapshot' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }
}
