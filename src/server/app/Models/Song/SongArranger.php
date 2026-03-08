<?php

declare(strict_types=1);

namespace App\Models\Song;

use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property string $song_id    楽曲ID
 * @property string $creator_id 編曲者ID
 * @property int    $order_no   表示順
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongArranger newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongArranger newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongArranger query()
 *
 * @mixin \Eloquent
 */
class SongArranger extends Model
{
    #[Override]
    protected $table = 'song_arrangers';

    // 複合主キーのため未設定
    #[Override]
    protected $primaryKey = '';

    #[Override]
    protected $keyType = 'string';

    #[Override]
    public $timestamps = false;
}
