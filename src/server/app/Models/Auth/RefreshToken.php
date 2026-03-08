<?php

declare(strict_types=1);

namespace App\Models\Auth;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property string          $refresh_token_id リフレッシュトークンID
 * @property string          $admin_user_id    管理ユーザーID
 * @property string          $token            トークン
 * @property CarbonImmutable $expired_at       有効期限
 * @property int             $status           消費状態
 * @property CarbonImmutable $created_at       作成日時
 * @property CarbonImmutable $updated_at       更新日時
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefreshToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefreshToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefreshToken query()
 *
 * @mixin \Eloquent
 */
class RefreshToken extends Model
{
    #[Override]
    protected $table = 'refresh_tokens';

    #[Override]
    protected $primaryKey = 'refresh_token_id';

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
