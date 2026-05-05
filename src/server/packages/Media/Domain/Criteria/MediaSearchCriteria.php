<?php

declare(strict_types=1);

namespace Media\Domain\Criteria;

use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\Optional;

readonly class MediaSearchCriteria
{
    /**
     * @param Optional<string> $title
     */
    public function __construct(
        public Optional $title,
        public int $page = 1,
        public PerPage $perPage = PerPage::TwentyFive,
    ) {
    }
}
