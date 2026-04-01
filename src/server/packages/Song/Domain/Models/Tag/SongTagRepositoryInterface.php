<?php

declare(strict_types=1);

namespace Song\Domain\Models\Tag;

interface SongTagRepositoryInterface
{
    /**
     * @return array<SongTag>
     */
    public function all(): array;

    public function findByName(SongTagName $name): ?SongTag;

    public function save(SongTag $tag): SongTag;

    public function getMaxOrderNo(): int;
}
