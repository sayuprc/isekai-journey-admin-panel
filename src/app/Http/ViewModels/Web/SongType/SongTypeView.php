<?php

declare(strict_types=1);

namespace App\Http\ViewModels\Web\SongType;

class SongTypeView
{
    public function __construct(
        public readonly string $songTypeId,
        public readonly string $songTypeName,
        public readonly int $orderNo,
    ) {
    }
}
