<?php

declare(strict_types=1);

namespace App\Models\Release;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property string          $release_id        リリースID
 * @property string          $title             タイトル
 * @property int             $type              種別
 * @property int             $distribution_type 流通形態
 * @property CarbonImmutable $released_on       発売日
 * @property string          $description       説明
 * @property bool            $is_display        表示フラグ
 * @property CarbonImmutable $created_at        作成日時
 * @property CarbonImmutable $updated_at        更新日時
 * @property-read Collection<int, TrackEntry> $trackEntries
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Release newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Release newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Release query()
 *
 * @mixin \Eloquent
 */
class Release extends Model
{
    #[Override]
    protected $table = 'releases';

    #[Override]
    protected $primaryKey = 'release_id';

    #[Override]
    protected $keyType = 'string';

    #[Override]
    protected function casts()
    {
        return [
            'distribution_type' => 'integer',
            'released_on' => 'immutable_date',
            'is_display' => 'bool',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return HasMany<TrackEntry, $this>
     */
    public function trackEntries(): HasMany
    {
        return $this->hasMany(TrackEntry::class, 'release_id', 'release_id')
            ->orderBy('track_no');
    }
}
