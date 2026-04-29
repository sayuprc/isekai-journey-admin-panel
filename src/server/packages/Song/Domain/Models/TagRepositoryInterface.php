<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use SongTag\Domain\Criteria\TagSearchCriteria;

interface TagRepositoryInterface
{
    /**
     * @return array<Tag>
     */
    public function all(): array;

    /**
     * @return array<Tag>
     */
    public function search(TagSearchCriteria $criteria): array;

    public function maxPage(TagSearchCriteria $criteria): int;

    public function findByName(TagName $name): ?Tag;

    public function getMaxOrderNo(): int;

    public function save(Tag $tag): Tag;
}
