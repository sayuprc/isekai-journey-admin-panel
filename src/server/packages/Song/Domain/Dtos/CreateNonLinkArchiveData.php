<?php

declare(strict_types=1);

namespace Song\Domain\Dtos;

use DateTimeInterface;

class CreateNonLinkArchiveData
{
    /**
     * @param positive-int $orderNo
     */
    public function __construct(
        public readonly DateTimeInterface $archivedOn,
        public readonly int $orderNo,
    ) {
    }
}
