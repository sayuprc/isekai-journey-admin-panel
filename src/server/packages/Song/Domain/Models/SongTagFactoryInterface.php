<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use Support\Domain\ValueObjects\OrderNo;

interface SongTagFactoryInterface
{
    public function create(SongTagId $songTagId, SongTagName $name, OrderNo $orderNo): SongTag;
}
