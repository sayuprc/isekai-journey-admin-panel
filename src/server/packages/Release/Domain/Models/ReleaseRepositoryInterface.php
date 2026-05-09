<?php

declare(strict_types=1);

namespace Release\Domain\Models;

use Release\Domain\Criteria\ReleaseSearchCriteria;

interface ReleaseRepositoryInterface
{
    /**
     * @return list<Release>
     */
    public function search(ReleaseSearchCriteria $criteria): array;

    public function maxPage(ReleaseSearchCriteria $criteria): int;

    public function save(Release $release): Release;
}
