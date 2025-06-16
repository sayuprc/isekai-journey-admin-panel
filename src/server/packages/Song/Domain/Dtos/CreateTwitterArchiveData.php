<?php

declare(strict_types=1);

namespace Song\Domain\Dtos;

use DateType\ImmutableDate;

class CreateTwitterArchiveData
{
    /**
     * @param positive-int $orderNo
     */
    public function __construct(
        public readonly string $archiveName,
        public readonly string $postUrl,
        public readonly ImmutableDate $archivedOn,
        public readonly int $orderNo,
    ) {
    }
}
