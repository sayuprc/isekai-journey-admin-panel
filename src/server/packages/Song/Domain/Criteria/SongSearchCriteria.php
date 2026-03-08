<?php

declare(strict_types=1);

namespace Song\Domain\Criteria;

use Song\Domain\Models\SongAttribute;
use Song\Domain\Models\SongType;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\Optional;

readonly class SongSearchCriteria
{
    /**
     * @param Optional<string>        $title
     * @param Optional<SongType>      $type
     * @param Optional<SongAttribute> $attribute
     */
    public function __construct(
        public Optional $title,
        public Optional $type,
        public Optional $attribute,
        public Sort $sort = Sort::OrderNo,
        public Order $order = Order::Asc,
        public int $page = 1,
        public PerPage $perPage = PerPage::Fifty,
    ) {
    }
}
