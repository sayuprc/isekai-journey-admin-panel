<?php

declare(strict_types=1);

namespace SongType\Domain\Services;

use SongType\Domain\Models\SongTypeId;
use SongType\Domain\Models\SongTypeName;
use SongType\Domain\Models\SongTypeRepositoryInterface;

class SongTypeNameDuplicateCheckService
{
    public function __construct(private readonly SongTypeRepositoryInterface $repository)
    {
    }

    public function exists(SongTypeName $songTypeName): bool
    {
        return ! is_null($this->repository->findByName($songTypeName));
    }

    public function existsForUpdate(SongTypeId $targetSongTypeId, SongTypeName $updatedSongTypeName): bool
    {
        $found = $this->repository->findByName($updatedSongTypeName);

        if (is_null($found)) {
            return false;
        }

        return $found->songTypeId->value !== $targetSongTypeId->value;
    }
}
