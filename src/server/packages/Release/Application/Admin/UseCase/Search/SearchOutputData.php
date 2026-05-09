<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Search;

use Release\Domain\Models\Release;

readonly class SearchOutputData
{
    /**
     * @param list<Release> $releases
     */
    public function __construct(
        public array $releases,
        public int $maxPage,
    ) {
    }
}
