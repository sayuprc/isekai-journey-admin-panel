<?php

declare(strict_types=1);

namespace SongType\Application\UseCase\Update;

use SongType\Domain\Models\SongType;

class UpdateOutputData
{
    public function __construct(public readonly SongType $songType)
    {
    }
}
