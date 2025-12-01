<?php

declare(strict_types=1);

namespace Song\Domain\Models;

interface SongRepositoryInterface
{
    /**
     * @return array<Song>
     */
    public function all(): array;

    public function save(Song $song): Song;
}
