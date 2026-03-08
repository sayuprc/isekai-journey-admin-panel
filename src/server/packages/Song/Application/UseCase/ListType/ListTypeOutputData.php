<?php

declare(strict_types=1);

namespace Song\Application\UseCase\ListType;

use Song\Domain\Models\SongType;

readonly class ListTypeOutputData
{
    /**
     * @param array<SongType> $types
     */
    public function __construct(public array $types)
    {
    }
}
