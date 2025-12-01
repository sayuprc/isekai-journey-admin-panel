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

    public function save(SongType $songType): SongType;

    public function delete(SongTypeId $songTypeId): void;
}
