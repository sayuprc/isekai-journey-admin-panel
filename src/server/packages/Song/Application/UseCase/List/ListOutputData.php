<?php

declare(strict_types=1);

namespace Song\Application\UseCase\List;

use Song\Domain\Models\Song;

readonly class ListOutputData
{
    /**
     * @param Song[] $songs
     */
    public function __construct(public array $songs)
    {
    }
}
