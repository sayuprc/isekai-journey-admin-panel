<?php

declare(strict_types=1);

namespace App\Models\Song;

use App\Models\Release\Release;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property string          $song_id     楽曲ID
 * @property string          $title       楽曲名
 * @property string          $description 説明
 * @property string|null     $lyrics_link 歌詞リンク
 * @property int             $type        種別
 * @property bool            $is_display  表示フラグ
 * @property int             $order_no    表示順
 * @property CarbonImmutable $created_at  作成日時
 * @property CarbonImmutable $updated_at  更新日時
 * @property-read Collection<int, SongPerson> $persons
 * @property-read Collection<int, SongTagging> $taggings
 * @property-read Collection<int, SongMediaLink> $songMediaLinks
 * @property-read Collection<int, Release> $releases
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
        'persons',
        'taggings',
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
     * @return HasMany<SongPerson, $this>
     */
    public function persons(): HasMany
    {
        return $this->hasMany(SongPerson::class, 'song_id', 'song_id')
            ->orderBy('order_no');
    }

    /**
     * @return HasMany<SongTagging, $this>
     */
    public function taggings(): HasMany
    {
        return $this->hasMany(SongTagging::class, 'song_id', 'song_id');
    }

    /**
     * @return HasMany<SongMediaLink, $this>
     */
    public function songMediaLinks(): HasMany
    {
        return $this->hasMany(SongMediaLink::class, 'song_id', 'song_id')
            ->orderBy('order_no');
    }

    /**
     * @return BelongsToMany<Release, $this>
     */
    public function releases(): BelongsToMany
    {
        return $this->belongsToMany(
            Release::class,
            'release_track_entries',
            'song_id',
            'release_id',
            'song_id',
            'release_id',
        )->withPivot('track_no');
    }
}
