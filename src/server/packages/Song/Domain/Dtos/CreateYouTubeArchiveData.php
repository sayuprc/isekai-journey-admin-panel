<?php

declare(strict_types=1);

namespace Song\Domain\Dtos;

use DateType\ImmutableDate;

class CreateYouTubeArchiveData
{
    /**
     * @param positive-int $orderNo
     */
    public function __construct(
        public readonly string $archiveName,
        public readonly string $videoUrl,
        public readonly string $thumbnailUrl,
        public readonly ImmutableDate $archivedOn,
        public readonly int $orderNo,
    ) {
    }
}
