<?php

declare(strict_types=1);

namespace SongType\Domain\Models;

use Support\Domain\ValueObjects\OrderNo;

class SongType
{
    public function __construct(
        public readonly SongTypeId $songTypeId,
        public readonly SongTypeName $songTypeName,
        public readonly OrderNo $orderNo,
    ) {
    }
}
