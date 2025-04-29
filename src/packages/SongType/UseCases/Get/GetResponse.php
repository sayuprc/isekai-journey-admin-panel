<?php

declare(strict_types=1);

namespace SongType\UseCases\Get;

use SongType\Domain\Models\SongType;

class GetResponse
{
    public function __construct(public readonly SongType $songType)
    {
    }
}
