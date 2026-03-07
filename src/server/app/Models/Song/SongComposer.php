<?php

declare(strict_types=1);

namespace App\Models\Song;

use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property string $song_id    楽曲ID
 * @property string $creator_id 作曲者ID
 * @property int    $order_no   表示順
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongComposer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongComposer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongComposer query()
 *
 * @mixin \Eloquent
 */
class SongComposer extends Model
{
    #[Override]
    protected $table = 'song_composers';

    // 複合主キーのため未設定
    #[Override]
    protected $primaryKey = '';

    #[Override]
    protected $keyType = 'string';

    #[Override]
    public $timestamps = false;
}
