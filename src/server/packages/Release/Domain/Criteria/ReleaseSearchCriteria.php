<?php

declare(strict_types=1);

namespace Release\Domain\Criteria;

use Release\Domain\Models\ReleaseDistributionType;
use Release\Domain\Models\ReleaseType;
use Support\Domain\SearchCriteria\PerPage;
use Support\Domain\ValueObjects\String\TextNormalizer;
use Support\Optional\Optional;
use Support\Optional\Some;

readonly class ReleaseSearchCriteria
{
    /** @var Optional<string> */
    public Optional $title;

    /**
     * @param Optional<string>                  $title
     * @param Optional<ReleaseType>             $type
     * @param Optional<ReleaseDistributionType> $distributionType
     * @param Optional<bool>                    $isDisplay
     */
    public function __construct(
        Optional $title,
        public Optional $type,
        public Optional $distributionType,
        public Optional $isDisplay,
        public int $page = 1,
        public PerPage $perPage = PerPage::TwentyFive,
    ) {
        // 保存側の ReleaseTitle と対称に、検索語も NFC へ揃える。
        $this->title = $title->isPresent()
            ? new Some(TextNormalizer::toNfc($title->get()))
            : $title;
    }
}
