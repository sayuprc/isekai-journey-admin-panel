<?php

declare(strict_types=1);

namespace App\Models\Song;

use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property string $song_id   楽曲ID
 * @property string $person_id 人物ID
 * @property string $role      役割
 * @property int    $order_no  表示順
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongPerson newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongPerson newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongPerson query()
 *
 * @mixin \Eloquent
 */
class SongPerson extends Model
{
    #[Override]
    protected $table = 'song_persons';

    #[Override]
    protected $primaryKey = '';

    #[Override]
    protected $keyType = 'string';

    #[Override]
    public $timestamps = false;
}
