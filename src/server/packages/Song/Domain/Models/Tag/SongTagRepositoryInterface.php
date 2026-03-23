<?php

declare(strict_types=1);

namespace Song\Domain\Models\Tag;

interface SongTagRepositoryInterface
{
    public function findByName(SongTagName $name): ?SongTag;

    public function save(SongTag $songTag): SongTag;

    public function getMaxOrderNo(): int;
}
