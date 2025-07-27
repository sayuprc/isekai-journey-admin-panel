<?php

declare(strict_types=1);

namespace SongType\Domain\Models;

use Support\Domain\ValueObjects\OrderNo;

readonly class SongType
{
    public function __construct(
        public SongTypeId $songTypeId,
        public SongTypeName $songTypeName,
        public OrderNo $orderNo,
    ) {
    }
}
