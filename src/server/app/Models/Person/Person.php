<?php

declare(strict_types=1);

namespace App\Models\Person;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property string          $person_id  人物ID
 * @property string          $name       人物名
 * @property int             $order_no   表示順
 * @property CarbonImmutable $created_at 作成日時
 * @property CarbonImmutable $updated_at 更新日時
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person query()
 *
 * @mixin \Eloquent
 */
class Person extends Model
{
    #[Override]
    protected $table = 'persons';

    #[Override]
    protected $primaryKey = 'person_id';

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
