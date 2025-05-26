<?php

declare(strict_types=1);

namespace SongType\Domain\Models;

interface SongTypeRepositoryInterface
{
    /**
     * @return array<SongType>
     */
    public function all(): array;

    public function find(SongTypeId $songTypeId): ?SongType;

    public function findByName(SongTypeName $songTypeName): ?SongType;

    public function insert(SongType $songType): void;

    public function update(SongType $songType): SongTypeId;

    public function delete(SongTypeId $songTypeId): void;
}
