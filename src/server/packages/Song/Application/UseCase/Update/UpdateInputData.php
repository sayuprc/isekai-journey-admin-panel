<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Update;

readonly class UpdateInputData
{
    /**
     * @param list<array{creatorId: string}> $lyricists
     * @param list<array{creatorId: string}> $composers
     * @param list<array{creatorId: string}> $arrangers
     * @param list<array{songTagId: string}> $tags
     */
    public function __construct(
        public string $songId,
        public string $title,
        public string $description,
        public int $typeValue,
        public bool $isDisplay,
        public int $orderNo,
        public array $lyricists,
        public array $composers,
        public array $arrangers,
        public array $tags = [],
    ) {
    }
}
