<?php

declare(strict_types=1);

namespace SongType\Application\UseCase\Create;

use SongType\Domain\Models\SongType;

class CreateOutputData
{
    public function __construct(public readonly SongType $songType)
    {
    }
}
