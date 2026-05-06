<?php

declare(strict_types=1);

namespace App\Models\Media;

use App\Models\Song\SongMediaLink;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property string          $media_id   メディアID
 * @property string          $title      タイトル
 * @property string               $url          URL
 * @property CarbonImmutable      $published_at 公開日
 * @property int                  $type         メディア種別
 * @property int                  $format       メディア形式
 * @property bool                 $is_display   表示フラグ
 * @property CarbonImmutable $created_at 作成日時
 * @property CarbonImmutable $updated_at 更新日時
 * @property-read Collection<int, SongMediaLink> $songMediaLinks
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media query()
 *
 * @mixin \Eloquent
 */
class Media extends Model
{
    #[Override]
    protected $table = 'media';

    #[Override]
    protected $primaryKey = 'media_id';

    #[Override]
    protected $keyType = 'string';

    #[Override]
    protected function casts()
    {
        return [
            'is_display' => 'bool',
            'published_at' => 'immutable_date',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return HasMany<SongMediaLink, $this>
     */
    public function songMediaLinks(): HasMany
    {
        return $this->hasMany(SongMediaLink::class, 'media_id', 'media_id');
    }
}
