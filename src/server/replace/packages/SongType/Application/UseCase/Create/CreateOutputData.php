<?php

declare(strict_types=1);

namespace SongType\Application\UseCase\Create;

use SongType\Domain\Models\SongType;

readonly class CreateOutputData
{
    public function __construct(public SongType $songType)
    {
    }
}
