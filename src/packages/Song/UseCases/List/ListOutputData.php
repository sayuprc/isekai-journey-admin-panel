<?php

declare(strict_types=1);

namespace Song\UseCases\List;

use Song\Domain\Models\Song;

class ListOutputData
{
    /**
     * @param Song[] $songs
     */
    public function __construct(public readonly array $songs)
    {
    }
}
