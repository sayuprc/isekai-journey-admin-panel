<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Create;

readonly class CreateInputData
{
    /**
     * @param list<array{songId: string, trackNo: int}> $trackEntries
     */
    public function __construct(
        public string $title,
        public int $typeValue,
        public int $distributionTypeValue,
        public string $releasedOn,
        public string $description,
        public bool $isDisplay,
        public array $trackEntries,
    ) {
    }
}
