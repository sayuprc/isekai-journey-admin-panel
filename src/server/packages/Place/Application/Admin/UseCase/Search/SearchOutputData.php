<?php

declare(strict_types=1);

namespace Place\Application\Admin\UseCase\Search;

use Place\Domain\Models\Place;

readonly class SearchOutputData
{
    /**
     * @param array<Place> $places
     */
    public function __construct(
        public array $places,
        public int $maxPage,
    ) {
    }
}
