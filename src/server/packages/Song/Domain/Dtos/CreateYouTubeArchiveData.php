<?php

declare(strict_types=1);

namespace Song\Domain\Dtos;

use DateTimeInterface;

class CreateYouTubeArchiveData
{
    /**
     * @param positive-int $orderNo
     */
    public function __construct(
        public readonly string $archiveName,
        public readonly string $videoUrl,
        public readonly string $thumbnailUrl,
        public readonly DateTimeInterface $archivedOn,
        public readonly int $orderNo,
    ) {
    }
}
