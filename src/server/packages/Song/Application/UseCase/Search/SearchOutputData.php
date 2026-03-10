<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Search;

use Song\Application\Query\SongSummary;

readonly class SearchOutputData
{
    /**
     * @param array<SongSummary> $songs
     */
    public function __construct(
        public array $songs,
        public int $maxPage,
    ) {
    }
}
