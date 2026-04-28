<?php

declare(strict_types=1);

namespace Song\Infrastructures;

use Override;
use Song\Domain\Models\Creators\Arrangers;
use Song\Domain\Models\Creators\Composers;
use Song\Domain\Models\Creators\Lyricists;
use Song\Domain\Models\Description;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongAttribute;
use Song\Domain\Models\SongFactoryInterface;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongType;
use Song\Domain\Models\Title;
use Support\Domain\ValueObjects\OrderNo;

readonly class SongFactory implements SongFactoryInterface
{
    #[Override]
    public function create(
        SongId $songId,
        Title $title,
        Description $description,
        SongType $type,
        ?SongAttribute $attribute,
        bool $isDisplay,
        OrderNo $orderNo,
        Lyricists $lyricists,
        Composers $composers,
        Arrangers $arrangers,
    ): Song {
        return new Song($songId, $title, $description, $type, $attribute, $isDisplay, $orderNo, $lyricists, $composers, $arrangers);
    }
}
