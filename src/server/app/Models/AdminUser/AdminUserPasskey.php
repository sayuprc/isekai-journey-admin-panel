<?php

declare(strict_types=1);

namespace App\Models\AdminUser;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property string                  $admin_user_passkey_id 管理ユーザーのパスキーID
 * @property string                  $admin_user_id         管理ユーザーID
 * @property string                  $user_handle           WebAuthn ユーザーハンドル
 * @property string                  $name                  パスキー名
 * @property string                  $credential_id         WebAuthn クレデンシャル ID
 * @property string                  $public_key            WebAuthn 公開鍵(COSE Key)
 * @property string                  $aaguid                認証器 AAGUID
 * @property array<array-key, mixed> $transports            認証器のトランスポート
 * @property bool|null               $backup_eligible       バックアップ可能か
 * @property bool|null               $backup_state          バックアップ済みか
 * @property int                     $sign_count            署名カウンター
 * @property CarbonImmutable|null    $last_used_at          最終利用日時
 * @property CarbonImmutable         $created_at            作成日時
 * @property CarbonImmutable         $updated_at            更新日時
 * @property-read AdminUser $adminUser
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUserPasskey newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUserPasskey newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUserPasskey query()
 *
 * @mixin \Eloquent
 */
class AdminUserPasskey extends Model
{
    #[Override]
    protected $table = 'admin_user_passkeys';

    #[Override]
    protected $primaryKey = 'admin_user_passkey_id';

    #[Override]
    protected $keyType = 'string';

    #[Override]
    protected function casts()
    {
        return [
            'transports' => 'array',
            'backup_eligible' => 'boolean',
            'backup_state' => 'boolean',
            'sign_count' => 'integer',
            'last_used_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<AdminUser, $this>
     */
    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id', 'admin_user_id');
    }
}
