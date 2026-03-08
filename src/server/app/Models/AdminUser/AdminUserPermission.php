<?php

declare(strict_types=1);

namespace App\Models\AdminUser;

use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property string $admin_user_id 管理ユーザーID
 * @property string $permission    権限
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUserPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUserPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUserPermission query()
 *
 * @mixin \Eloquent
 */
class AdminUserPermission extends Model
{
    #[Override]
    protected $table = 'admin_user_permissions';

    // 複合主キーのため未設定
    #[Override]
    protected $primaryKey = '';

    #[Override]
    protected $keyType = 'string';

    #[Override]
    public $timestamps = false;
}
