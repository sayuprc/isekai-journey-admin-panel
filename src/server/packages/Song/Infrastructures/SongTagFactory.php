<?php

declare(strict_types=1);

namespace Song\Infrastructures;

use Override;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagFactoryInterface;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagName;
use Support\Domain\ValueObjects\OrderNo;

class SongTagFactory implements SongTagFactoryInterface
{
    #[Override]
    public function create(SongTagId $songTagId, SongTagName $name, OrderNo $orderNo): SongTag
    {
        return new SongTag($songTagId, $name, $orderNo);
    }
}
