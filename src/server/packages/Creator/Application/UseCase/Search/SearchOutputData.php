<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\Search;

use Creator\Domain\Models\Creator;

readonly class SearchOutputData
{
    /**
     * @param array<Creator> $creators
     */
    public function __construct(
        public array $creators,
        public int $maxPage,
    ) {
    }
}
