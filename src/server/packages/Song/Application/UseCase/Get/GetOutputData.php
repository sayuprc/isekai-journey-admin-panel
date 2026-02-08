<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Get;

use Song\Application\Assemble\AssembledSong;

readonly class GetOutputData
{
    public function __construct(public AssembledSong $song)
    {
    }
}
