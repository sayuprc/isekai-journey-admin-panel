<?php

declare(strict_types=1);

namespace App\Features\Song\Port\List;

use App\Features\Song\Domain\Entities\Song;

class ListResponse
{
    /**
     * @param Song[] $songs
     */
    public function __construct(public readonly array $songs)
    {
    }
}
