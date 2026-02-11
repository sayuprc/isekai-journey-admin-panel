<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Update;

readonly class UpdateInputData
{
    /**
     * @param list<array{creatorId: string}> $arrangers
     * @param list<array{creatorId: string}> $composers
     * @param list<array{creatorId: string}> $lyricists
     */
    public function __construct(
        public string $songId,
        public string $title,
        public string $description,
        public int $songTypeValue,
        public int $orderNo,
        public array $arrangers,
        public array $composers,
        public array $lyricists,
    ) {
    }
}
