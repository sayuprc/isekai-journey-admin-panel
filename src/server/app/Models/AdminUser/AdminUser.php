<?php

declare(strict_types=1);

namespace App\Models\AdminUser;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property string          $admin_user_id 管理ユーザーID
 * @property string          $name          管理者名
 * @property string          $email         メールアドレス
 * @property string          $password      パスワード
 * @property int             $role          ロール
 * @property CarbonImmutable $created_at    作成日時
 * @property CarbonImmutable $updated_at    更新日時
 * @property-read Collection<int, AdminUserPermission> $permissions
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUser query()
 *
 * @mixin \Eloquent
 */
class AdminUser extends Model
{
    #[Override]
    protected $table = 'admin_users';

    #[Override]
    protected $primaryKey = 'admin_user_id';

    #[Override]
    protected $keyType = 'string';

    #[Override]
    protected $with = ['permissions'];

    #[Override]
    protected function casts()
    {
        return [
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return HasMany<AdminUserPermission, $this>
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(AdminUserPermission::class, 'admin_user_id', 'admin_user_id');
    }
}
