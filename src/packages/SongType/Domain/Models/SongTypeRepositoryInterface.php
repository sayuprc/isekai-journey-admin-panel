<?php

declare(strict_types=1);

namespace SongType\Domain\Models;

interface SongTypeRepositoryInterface
{
    /**
     * @return array<SongType>
     */
    public function all(): array;
}
