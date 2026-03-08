<?php

declare(strict_types=1);

namespace App\Models\Creator;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property string          $creator_id クリエイターID
 * @property string          $name       クリエイター名
 * @property int             $order_no   表示順
 * @property CarbonImmutable $created_at 作成日時
 * @property CarbonImmutable $updated_at 更新日時
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Creator newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Creator newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Creator query()
 *
 * @mixin \Eloquent
 */
class Creator extends Model
{
    #[Override]
    protected $table = 'creators';

    #[Override]
    protected $primaryKey = 'creator_id';

    #[Override]
    protected $keyType = 'string';

    #[Override]
    protected function casts()
    {
        return [
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
