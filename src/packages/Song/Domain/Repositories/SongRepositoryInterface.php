<?php

declare(strict_types=1);

namespace Song\Domain\Repositories;

use Song\Domain\Entities\Song;

interface SongRepositoryInterface
{
    /**
     * @return Song[]
     */
    public function listSongs(): array;
}
