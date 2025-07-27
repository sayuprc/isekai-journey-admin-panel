<?php

declare(strict_types=1);

namespace SongType\Application\UseCase\Get;

use SongType\Domain\Models\SongType;

readonly class GetOutputData
{
    public function __construct(public SongType $songType)
    {
    }
}
