<?php

declare(strict_types=1);

namespace App\Models\AdminUser;

use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property string $admin_user_registration_token_id 管理ユーザー登録トークンID
 * @property string $permission                       権限
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistrationTokenPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistrationTokenPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistrationTokenPermission query()
 *
 * @mixin \Eloquent
 */
class RegistrationTokenPermission extends Model
{
    #[Override]
    protected $table = 'admin_user_registration_token_permissions';

    // 複合主キーのため未設定
    #[Override]
    protected $primaryKey = '';

    #[Override]
    protected $keyType = 'string';

    #[Override]
    public $timestamps = false;
}
