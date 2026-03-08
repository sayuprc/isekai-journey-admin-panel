<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use Creator\Domain\Models\CreatorId;
use Song\Domain\Criteria\SongSearchCriteria;

interface SongRepositoryInterface
{
    /**
     * @return array<Song>
     */
    public function all(): array;

    /**
     * @return array<Song>
     */
    public function search(SongSearchCriteria $criteria): array;

    public function maxPage(SongSearchCriteria $criteria): int;

    public function find(SongId $songId): ?Song;

    public function isCreatorUsed(CreatorId $creatorId): bool;

    public function save(Song $song): Song;

    public function delete(SongId $songId): void;

    public function getMaxOrderNo(): int;
}
