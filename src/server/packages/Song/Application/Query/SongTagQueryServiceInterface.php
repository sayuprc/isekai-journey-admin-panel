<?php

declare(strict_types=1);

namespace Song\Application\Query;

use Song\Domain\Criteria\SongTagSearchCriteria;
use Song\Domain\Models\Tag\SongTag;

interface SongTagQueryServiceInterface
{
    /**
     * @return array<SongTag>
     */
    public function search(SongTagSearchCriteria $criteria): array;

    public function maxPage(SongTagSearchCriteria $criteria): int;
}
