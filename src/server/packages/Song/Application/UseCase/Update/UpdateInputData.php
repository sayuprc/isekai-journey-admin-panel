<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Update;

readonly class UpdateInputData
{
    /**
     * @param list<array{songTagId: string}>                            $tags
     * @param list<array{personId: string, role: string, orderNo: int}> $persons
     */
    public function __construct(
        public string $songId,
        public string $title,
        public string $description,
        public ?string $lyricsLink,
        public int $typeValue,
        public bool $isDisplay,
        public int $orderNo,
        public array $tags,
        public array $persons,
    ) {
    }
}
