<?php

declare(strict_types=1);

namespace App\Models\Performer;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property string          $performer_id 共演者ID
 * @property string          $name         共演者名
 * @property int             $order_no     表示順
 * @property CarbonImmutable $created_at   作成日時
 * @property CarbonImmutable $updated_at   更新日時
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Performer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Performer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Performer query()
 *
 * @mixin \Eloquent
 */
class Performer extends Model
{
    #[Override]
    protected $table = 'performers';

    #[Override]
    protected $primaryKey = 'performer_id';

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
