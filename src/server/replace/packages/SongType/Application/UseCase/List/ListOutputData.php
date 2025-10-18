<?php

declare(strict_types=1);

namespace SongType\Application\UseCase\List;

use SongType\Domain\Models\SongType;

readonly class ListOutputData
{
    /**
     * @param array<SongType> $songTypes
     */
    public function __construct(public array $songTypes)
    {
    }
}
