<?php

declare(strict_types=1);

namespace SongTag\Application\UseCase\Search;

use Song\Domain\Models\Tag;

readonly class SearchOutputData
{
    /**
     * @param array<Tag> $tags
     */
    public function __construct(
        public array $tags,
        public int $maxPage,
    ) {
    }
}
