<?php

declare(strict_types=1);

namespace Song\Application\UseCase\List;

use Song\Application\Assemble\AssembledSong;

readonly class ListOutputData
{
    /**
     * @param array<AssembledSong> $songs
     */
    public function __construct(public array $songs)
    {
    }
}
