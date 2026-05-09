<?php

declare(strict_types=1);

namespace Release\Domain\Criteria;

use Release\Domain\Models\ReleaseDistributionType;
use Release\Domain\Models\ReleaseType;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\Optional;

readonly class ReleaseSearchCriteria
{
    /**
     * @param Optional<string>                  $title
     * @param Optional<ReleaseType>             $type
     * @param Optional<ReleaseDistributionType> $distributionType
     * @param Optional<bool>                    $isDisplay
     */
    public function __construct(
        public Optional $title,
        public Optional $type,
        public Optional $distributionType,
        public Optional $isDisplay,
        public int $page = 1,
        public PerPage $perPage = PerPage::TwentyFive,
    ) {
    }
}
