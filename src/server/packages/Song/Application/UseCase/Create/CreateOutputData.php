<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Create;

use Song\Application\Assemble\AssembledSong;

readonly class CreateOutputData
{
    public function __construct(public AssembledSong $song)
    {
    }
}
