<?php

declare(strict_types=1);

namespace SongType\Application\UseCase\Get;

use SongType\Domain\Models\SongType;

class GetOutputData
{
    public function __construct(public readonly SongType $songType)
    {
    }
}
