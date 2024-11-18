<?php

declare(strict_types=1);

namespace App\Features\Song\Domain\Repositories;

use App\Features\Song\Domain\Entities\Song;

interface SongRepositoryInterface
{
    /**
     * @return Song[]
     */
    public function listSongs(): array;
}
