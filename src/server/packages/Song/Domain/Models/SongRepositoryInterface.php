<?php

declare(strict_types=1);

namespace Song\Domain\Models;

interface SongRepositoryInterface
{
    public function save(Song $song): Song;
}
