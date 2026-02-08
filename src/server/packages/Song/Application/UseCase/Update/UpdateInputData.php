<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Update;

readonly class UpdateInputData
{
    /**
     * @param array<int, array{creatorId: string, orderNo: int}> $arrangers
     * @param array<int, array{creatorId: string, orderNo: int}> $composers
     * @param array<int, array{creatorId: string, orderNo: int}> $lyricists
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
