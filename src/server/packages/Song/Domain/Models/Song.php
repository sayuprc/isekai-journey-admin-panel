<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use Song\Domain\Models\Creators\Arrangers;
use Song\Domain\Models\Creators\Composers;
use Song\Domain\Models\Creators\Lyricists;
use SongType\Domain\Models\SongType;
use Support\Domain\ValueObjects\OrderNo;

readonly class Song
{
    public function __construct(
        public SongId $songId,
        public Title $title,
        public Description $description,
        public SongType $songType,
        public OrderNo $orderNo,
        public Arrangers $arrangers,
        public Composers $composers,
        public Lyricists $lyricists,
    ) {
    }
}
