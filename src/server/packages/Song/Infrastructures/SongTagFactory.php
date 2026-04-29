<?php

declare(strict_types=1);

namespace Song\Infrastructures;

use Override;
use Song\Domain\Models\SongTag;
use Song\Domain\Models\SongTagFactoryInterface;
use Song\Domain\Models\SongTagId;
use Song\Domain\Models\SongTagName;
use Support\Domain\ValueObjects\OrderNo;

readonly class SongTagFactory implements SongTagFactoryInterface
{
    #[Override]
    public function create(SongTagId $songTagId, SongTagName $name, OrderNo $orderNo): SongTag
    {
        return new SongTag($songTagId, $name, $orderNo);
    }
}
