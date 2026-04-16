<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use Song\Domain\Models\Creators\Arrangers;
use Song\Domain\Models\Creators\Composers;
use Song\Domain\Models\Creators\Lyricists;
use Support\Domain\ValueObjects\OrderNo;

interface SongFactoryInterface
{
    public function create(
        SongId $songId,
        Title $title,
        Description $description,
        SongType $type,
        ?SongAttribute $attribute,
        OrderNo $orderNo,
        bool $isDisplay = true,
        Lyricists $lyricists,
        Composers $composers,
        Arrangers $arrangers,
    ): Song;
}
