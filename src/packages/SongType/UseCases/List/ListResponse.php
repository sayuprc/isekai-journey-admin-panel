<?php

declare(strict_types=1);

namespace SongType\UseCases\List;

use SongType\Domain\Models\SongType;

class ListResponse
{
    /**
     * @param array<SongType> $songTypes
     */
    public function __construct(public readonly array $songTypes)
    {
    }
}
