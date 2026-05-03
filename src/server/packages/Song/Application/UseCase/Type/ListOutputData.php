<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Type;

use Song\Domain\Models\SongType;

readonly class ListOutputData
{
    /**
     * @param array<SongType> $types
     */
    public function __construct(public array $types)
    {
    }
}
