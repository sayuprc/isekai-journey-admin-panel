<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use Song\Domain\Models\Creators\Arrangers;
use Song\Domain\Models\Creators\Composers;
use Song\Domain\Models\Creators\Lyricists;
use Song\Domain\Models\Tags\SongTagReferences;
use Support\Domain\ValueObjects\OrderNo;

interface SongFactoryInterface
{
    public function create(
        SongId $songId,
        Title $title,
        Description $description,
        SongType $type,
        bool $isDisplay,
        OrderNo $orderNo,
        SongTagReferences $tags,
        Lyricists $lyricists,
        Composers $composers,
        Arrangers $arrangers,
    ): Song;
}
