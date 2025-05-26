<?php

declare(strict_types=1);

namespace Song\Domain\Repositories;

use Song\Domain\Models\Song;

interface SongRepositoryInterface
{
    /**
     * @return array<Song>
     */
    public function all(): array;

    public function insert(Song $song): void;
}
