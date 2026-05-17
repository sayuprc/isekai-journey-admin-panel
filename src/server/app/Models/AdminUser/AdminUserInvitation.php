<?php

declare(strict_types=1);

namespace App\Models\AdminUser;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property string               $invitation_id 招待ID
 * @property string               $token_hash    招待トークンのハッシュ
 * @property int                  $role          ロール
 * @property CarbonImmutable      $expires_at    有効期限
 * @property CarbonImmutable|null $consumed_at   消費日時
 * @property CarbonImmutable      $created_at    作成日時
 * @property CarbonImmutable      $updated_at    更新日時
 * @property-read Collection<int, AdminUserInvitationPermission> $permissions
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUserInvitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUserInvitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUserInvitation query()
 *
 * @mixin \Eloquent
 */
class AdminUserInvitation extends Model
{
    #[Override]
    protected $table = 'admin_user_invitations';

    #[Override]
    protected $primaryKey = 'invitation_id';

    #[Override]
    protected $keyType = 'string';

    #[Override]
    protected $with = ['permissions'];

    #[Override]
    protected function casts()
    {
        return [
            'expires_at' => 'immutable_datetime',
            'consumed_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return HasMany<AdminUserInvitationPermission, $this>
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(AdminUserInvitationPermission::class, 'invitation_id', 'invitation_id');
    }
}
