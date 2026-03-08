<?php

declare(strict_types=1);

namespace Performer\Application\UseCase\Search;

use Performer\Domain\Models\Performer;

readonly class SearchOutputData
{
    /**
     * @param array<Performer> $performers
     */
    public function __construct(
        public array $performers,
        public int $maxPage,
    ) {
    }
}
