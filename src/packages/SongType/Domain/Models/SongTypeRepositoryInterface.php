<?php

declare(strict_types=1);

namespace SongType\Domain\Models;

interface SongTypeRepositoryInterface
{
    /**
     * @return array<SongType>
     */
    public function all(): array;

    public function findByName(SongTypeName $songTypeName): ?SongType;

    public function insert(SongType $songType): void;
}
