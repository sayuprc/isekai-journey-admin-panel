<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use Song\Domain\Models\Creators\Arrangers;
use Song\Domain\Models\Creators\Composers;
use Song\Domain\Models\Creators\Lyricists;
use SongType\Domain\Models\SongType;
use Support\Domain\ValueObjects\OrderNo;

interface SongFactoryInterface
{
    public function create(
        SongId $songId,
        Title $title,
        Description $description,
        SongType $songType,
        OrderNo $orderNo,
        Lyricists $lyricists,
        Composers $composers,
        Arrangers $arrangers,
    ): Song;
}
