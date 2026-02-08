<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Update;

use Song\Application\Assemble\AssembledSong;

readonly class UpdateOutputData
{
    public function __construct(public AssembledSong $song)
    {
    }
}
