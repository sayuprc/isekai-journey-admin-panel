<?php

declare(strict_types=1);

namespace App\Models\Song;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property string          $song_tag_id 楽曲タグID
 * @property string          $name        楽曲タグ名
 * @property int             $order_no    表示順
 * @property CarbonImmutable $created_at  作成日時
 * @property CarbonImmutable $updated_at  更新日時
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongTag query()
 *
 * @mixin \Eloquent
 */
class SongTag extends Model
{
    #[Override]
    protected $table = 'song_tags';

    #[Override]
    protected $primaryKey = 'song_tag_id';

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
