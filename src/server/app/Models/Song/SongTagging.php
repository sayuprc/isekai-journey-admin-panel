<?php

declare(strict_types=1);

namespace App\Models\Song;

use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property string $song_id     楽曲ID
 * @property string $song_tag_id 楽曲タグID
 * @property int    $order_no    表示順
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongTagging newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongTagging newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongTagging query()
 *
 * @mixin \Eloquent
 */
class SongTagging extends Model
{
    #[Override]
    protected $table = 'song_taggings';

    // 複合主キーのため未設定
    #[Override]
    protected $primaryKey = '';

    #[Override]
    protected $keyType = 'string';

    #[Override]
    public $timestamps = false;
}
