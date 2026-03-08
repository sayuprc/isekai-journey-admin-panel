<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Search;

use Song\Application\Assemble\AssembledSong;

readonly class SearchOutputData
{
    /**
     * @param array<AssembledSong> $songs
     */
    public function __construct(
        public array $songs,
        public int $maxPage,
    ) {
    }
}
