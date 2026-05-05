<?php

declare(strict_types=1);

namespace Media\Application\UseCase\Search;

use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\Arg;

readonly class SearchInputData
{
    public function __construct(
        public Arg|string $title = Arg::Optional,
        public int $page = 1,
        public PerPage $perPage = PerPage::TwentyFive,
    ) {
    }
}
