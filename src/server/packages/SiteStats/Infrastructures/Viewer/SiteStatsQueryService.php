<?php

declare(strict_types=1);

namespace SiteStats\Infrastructures\Viewer;

use Override;
use SiteStats\Application\Viewer\Query\SiteStats;
use SiteStats\Application\Viewer\Query\SiteStatsQueryServiceInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class SiteStatsQueryService implements SiteStatsQueryServiceInterface
{
    public function __construct(private QueryFactory $queryFactory)
    {
    }

    #[Override]
    public function get(): SiteStats
    {
        $songCount = Row::intValue(
            $this->queryFactory->select()
                ->from('songs')
                ->where('is_display', '=', true)
                ->aggregate($this->queryFactory->pdo(), 'COUNT(*)'),
        );

        return new SiteStats($songCount);
    }
}
