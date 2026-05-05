<?php

declare(strict_types=1);

namespace App\Models\AdminUser;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property string          $admin_user_registration_token_id 管理ユーザー登録トークンID
 * @property string          $name                             管理者名
 * @property string          $email                            メールアドレス
 * @property int             $role                             ロール
 * @property list<string>    $permissions                      権限一覧
 * @property string          $token_hash                       登録トークンハッシュ
 * @property CarbonImmutable $expired_at                       有効期限
 * @property CarbonImmutable|null $used_at                     使用日時
 * @property CarbonImmutable $created_at                       作成日時
 * @property CarbonImmutable $updated_at                       更新日時
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUserRegistrationToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUserRegistrationToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUserRegistrationToken query()
 *
 * @mixin \Eloquent
 */
class AdminUserRegistrationToken extends Model
{
    #[Override]
    protected $table = 'admin_user_registration_tokens';

    #[Override]
    protected $primaryKey = 'admin_user_registration_token_id';

    #[Override]
    protected $keyType = 'string';

    #[Override]
    protected function casts()
    {
        return [
            'permissions' => 'array',
            'expired_at' => 'immutable_datetime',
            'used_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
