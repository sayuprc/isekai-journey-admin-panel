<?php

declare(strict_types=1);

namespace App\Models\Song;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property string          $song_id     楽曲ID
 * @property string          $title       楽曲名
 * @property string          $description 説明
 * @property int             $type        種別
 * @property int|null        $attribute   属性
 * @property bool            $is_display  表示フラグ
 * @property int             $order_no    表示順
 * @property CarbonImmutable $created_at  作成日時
 * @property CarbonImmutable $updated_at  更新日時
 * @property-read Collection<int, SongArranger> $arrangers
 * @property-read Collection<int, SongComposer> $composers
 * @property-read Collection<int, SongLyricist> $lyricists
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Song newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Song newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Song query()
 *
 * @mixin \Eloquent
 */
class Song extends Model
{
    #[Override]
    protected $table = 'songs';

    #[Override]
    protected $primaryKey = 'song_id';

    #[Override]
    protected $keyType = 'string';

    #[Override]
    protected $with = [
        'lyricists',
        'composers',
        'arrangers',
    ];

    #[Override]
    protected function casts()
    {
        return [
            'is_display' => 'bool',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return HasMany<SongLyricist, $this>
     */
    public function lyricists(): HasMany
    {
        return $this->hasMany(SongLyricist::class, 'song_id', 'song_id');
    }

    /**
     * @return HasMany<SongComposer, $this>
     */
    public function composers(): HasMany
    {
        return $this->hasMany(SongComposer::class, 'song_id', 'song_id');
    }

    /**
     * @return HasMany<SongArranger, $this>
     */
    public function arrangers(): HasMany
    {
        return $this->hasMany(SongArranger::class, 'song_id', 'song_id');
    }
}
