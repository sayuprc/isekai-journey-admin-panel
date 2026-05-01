<?php

declare(strict_types=1);

namespace Song\Application\Assemble;

readonly class AssembledSong
{
    /**
     * @param array<int, AssembledCreator> $lyricists
     * @param array<int, AssembledCreator> $composers
     * @param array<int, AssembledCreator> $arrangers
     * @param array<int, AssembledTag>     $tags
     */
    public function __construct(
        public string $songId,
        public string $title,
        public string $description,
        public string $typeName,
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
