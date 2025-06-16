<?php

declare(strict_types=1);

namespace Song\Domain\Dtos;

use DateType\ImmutableDate;

class CreateNonLinkArchiveData
{
    /**
     * @param positive-int $orderNo
     */
    public function __construct(
        public readonly ImmutableDate $archivedOn,
        public readonly int $orderNo,
    ) {
    }
}
