<?php

declare(strict_types=1);

namespace Media\Domain\Criteria;

use Media\Domain\Models\MediaFormat;
use Media\Domain\Models\MediaType;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\Optional;

readonly class MediaSearchCriteria
{
    /**
     * @param Optional<string>      $title
     * @param Optional<MediaType>   $type
     * @param Optional<MediaFormat> $format
     * @param Optional<bool>        $isDisplay
     */
    public function __construct(
        public Optional $title,
        public Optional $type,
        public Optional $format,
        public Optional $isDisplay,
        public int $page = 1,
        public PerPage $perPage = PerPage::TwentyFive,
    ) {
    }
}
