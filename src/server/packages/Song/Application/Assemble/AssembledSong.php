<?php

declare(strict_types=1);

namespace Song\Application\Assemble;

readonly class AssembledSong
{
    /**
     * @param array<int, AssembledCreator> $arrangers
     * @param array<int, AssembledCreator> $composers
     * @param array<int, AssembledCreator> $lyricists
     */
    public function __construct(
        public string $songId,
        public string $title,
        public string $description,
        public string $songTypeName,
        public int $songTypeValue,
        public int $orderNo,
        public array $arrangers,
        public array $composers,
        public array $lyricists,
    ) {
    }
}
