<?php

declare(strict_types=1);

namespace App\Models\AdminUser;

use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property string $invitation_id 招待ID
 * @property string $permission    権限
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUserInvitationPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUserInvitationPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUserInvitationPermission query()
 *
 * @mixin \Eloquent
 */
class AdminUserInvitationPermission extends Model
{
    #[Override]
    protected $table = 'admin_user_invitation_permissions';

    // 複合主キーのため未設定
    #[Override]
    protected $primaryKey = '';

    #[Override]
    protected $keyType = 'string';

    #[Override]
    public $timestamps = false;
}
