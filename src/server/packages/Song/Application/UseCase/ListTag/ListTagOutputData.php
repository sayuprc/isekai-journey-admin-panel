<?php

declare(strict_types=1);

namespace Song\Application\UseCase\ListTag;

use Song\Domain\Models\Tag\SongTag;

readonly class ListTagOutputData
{
    /**
     * @param array<SongTag> $tags
     */
    public function __construct(public array $tags)
    {
    }
}
