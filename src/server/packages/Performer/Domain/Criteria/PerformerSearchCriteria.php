<?php

declare(strict_types=1);

namespace Performer\Domain\Criteria;

use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\Optional;

readonly class PerformerSearchCriteria
{
    /**
     * @param Optional<string> $name
     */
    public function __construct(
        public Optional $name,
        public Sort $sort = Sort::OrderNo,
        public Order $order = Order::Asc,
        public int $page = 1,
        public PerPage $perPage = PerPage::Fifty,
    ) {
    }
}
