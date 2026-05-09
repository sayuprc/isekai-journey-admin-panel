<?php

declare(strict_types=1);

namespace App\Models\Release;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property string $release_id リリースID
 * @property string $song_id    楽曲ID
 * @property int    $track_no   曲順
 * @property-read Release $release
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackEntry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackEntry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackEntry query()
 *
 * @mixin \Eloquent
 */
class TrackEntry extends Model
{
    #[Override]
    protected $table = 'release_track_entries';

    // 複合主キーのため未設定
    #[Override]
    protected $primaryKey = '';

    #[Override]
    protected $keyType = 'string';

    #[Override]
    public $timestamps = false;

    /**
     * @return BelongsTo<Release, $this>
     */
    public function release(): BelongsTo
    {
        return $this->belongsTo(Release::class, 'release_id', 'release_id');
    }
}
