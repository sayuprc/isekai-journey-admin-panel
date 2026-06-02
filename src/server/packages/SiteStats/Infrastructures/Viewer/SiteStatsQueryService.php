<?php

declare(strict_types=1);

namespace SiteStats\Infrastructures\Viewer;

use App\Models\Song\Song;
use Override;
use SiteStats\Application\Viewer\Query\SiteStats;
use SiteStats\Application\Viewer\Query\SiteStatsQueryServiceInterface;

class SiteStatsQueryService implements SiteStatsQueryServiceInterface
{
    #[Override]
    public function get(): SiteStats
    {
        return new SiteStats(
            Song::query()
                ->where('is_display', true)
                ->count(),
        );
    }
}
