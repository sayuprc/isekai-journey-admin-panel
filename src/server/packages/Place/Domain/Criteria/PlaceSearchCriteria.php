<?php

declare(strict_types=1);

namespace Place\Domain\Criteria;

use Place\Domain\Models\PlaceKind;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Domain\ValueObjects\String\TextNormalizer;
use Support\Optional\Optional;
use Support\Optional\Some;

readonly class PlaceSearchCriteria
{
    /** @var Optional<string> */
    public Optional $name;

    /**
     * @param Optional<string>    $name
     * @param Optional<PlaceKind> $kind
     */
    public function __construct(
        Optional $name,
        public Optional $kind,
        public Sort $sort = Sort::Name,
        public Order $order = Order::Asc,
        public int $page = 1,
        public PerPage $perPage = PerPage::Fifty,
    ) {
        // 保存側の PlaceName と対称に、検索語も NFC へ揃える
        $this->name = $name->isPresent()
            ? new Some(TextNormalizer::toNfc($name->get()))
            : $name;
    }
}
