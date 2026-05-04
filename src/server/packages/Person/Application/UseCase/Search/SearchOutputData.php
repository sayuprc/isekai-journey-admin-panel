<?php

declare(strict_types=1);

namespace Person\Application\UseCase\Search;

use Person\Domain\Models\Person;

readonly class SearchOutputData
{
    /**
     * @param array<Person> $persons
     */
    public function __construct(
        public array $persons,
        public int $maxPage,
    ) {
    }
}
