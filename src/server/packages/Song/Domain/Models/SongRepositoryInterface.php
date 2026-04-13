<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use Creator\Domain\Models\CreatorId;
use Song\Domain\Models\Tag\SongTagId;

interface SongRepositoryInterface
{
    public function find(SongId $songId): ?Song;

    public function isCreatorUsed(CreatorId $creatorId): bool;

    public function isSongTagUsed(SongTagId $songTagId): bool;

    public function save(Song $song): Song;

    public function delete(SongId $songId): void;

    public function getMaxOrderNo(): int;
}
