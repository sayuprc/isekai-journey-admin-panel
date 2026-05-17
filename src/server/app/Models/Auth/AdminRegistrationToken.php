<?php

declare(strict_types=1);

namespace App\Models\Auth;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property string          $admin_registration_token_id 管理画面ユーザー登録トークンID
 * @property string          $email                       発行対象メールアドレス
 * @property int             $role                        ロール
 * @property string          $token                       登録トークン
 * @property CarbonImmutable $expired_at                  有効期限
 * @property int             $status                      消費状態
 * @property CarbonImmutable $created_at                  作成日時
 * @property CarbonImmutable $updated_at                  更新日時
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminRegistrationToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminRegistrationToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminRegistrationToken query()
 *
 * @mixin \Eloquent
 */
class AdminRegistrationToken extends Model
{
    #[Override]
    protected $table = 'admin_registration_tokens';

    #[Override]
    protected $primaryKey = 'admin_registration_token_id';

    #[Override]
    protected $keyType = 'string';

    #[Override]
    protected function casts()
    {
        return [
            'expired_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
