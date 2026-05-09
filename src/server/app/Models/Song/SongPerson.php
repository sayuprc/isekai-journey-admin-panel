<?php

declare(strict_types=1);

namespace App\Models\Song;

use App\Models\Person\Person;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property string $song_id   楽曲ID
 * @property string $person_id 人物ID
 * @property string $role      役割
 * @property int    $order_no  表示順
 * @property-read Person $person
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongPerson newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongPerson newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SongPerson query()
 *
 * @mixin \Eloquent
 */
class SongPerson extends Model
{
    #[Override]
    protected $table = 'song_persons';

    #[Override]
    protected $primaryKey = '';

    #[Override]
    protected $keyType = 'string';

    #[Override]
    public $timestamps = false;

    /**
     * @return BelongsTo<Person, $this>
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id', 'person_id');
    }
}
