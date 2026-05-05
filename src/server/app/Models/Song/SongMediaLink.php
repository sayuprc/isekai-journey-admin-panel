<?php

declare(strict_types=1);

namespace App\Models\Song;

use App\Models\Media\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property string $song_id  楽曲ID
 * @property string $media_id メディアID
 * @property int    $order_no 表示順
 * @property-read Song $song
 * @property-read Media $media
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongMediaLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongMediaLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongMediaLink query()
 *
 * @mixin \Eloquent
 */
class SongMediaLink extends Model
{
    #[Override]
    protected $table = 'song_media_links';

    // 複合主キーのため未設定
    #[Override]
    protected $primaryKey = '';

    #[Override]
    protected $keyType = 'string';

    #[Override]
    public $timestamps = false;

    /**
     * @return BelongsTo<Song, $this>
     */
    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class, 'song_id', 'song_id');
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id', 'media_id');
    }
}
