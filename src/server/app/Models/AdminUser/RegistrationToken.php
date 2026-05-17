<?php

declare(strict_types=1);

namespace App\Models\AdminUser;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property string          $admin_user_registration_token_id 管理ユーザー登録トークンID
 * @property string          $token                            トークン
 * @property string          $email                            メールアドレス
 * @property int             $role                             ロール
 * @property CarbonImmutable $expired_at                       有効期限
 * @property int             $status                           消費状態
 * @property CarbonImmutable $created_at                       作成日時
 * @property CarbonImmutable $updated_at                       更新日時
 * @property-read Collection<int, RegistrationTokenPermission> $permissions
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistrationToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistrationToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistrationToken query()
 *
 * @mixin \Eloquent
 */
class RegistrationToken extends Model
{
    #[Override]
    protected $table = 'admin_user_registration_tokens';

    #[Override]
    protected $primaryKey = 'admin_user_registration_token_id';

    #[Override]
    protected $keyType = 'string';

    #[Override]
    protected $with = ['permissions'];

    #[Override]
    protected function casts()
    {
        return [
            'expired_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return HasMany<RegistrationTokenPermission, $this>
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(
            RegistrationTokenPermission::class,
            'admin_user_registration_token_id',
            'admin_user_registration_token_id',
        );
    }
}
