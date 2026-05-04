<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Create;

readonly class CreateInputData
{
    /**
     * @param list<array{songTagId: string}>                            $tags
     * @param list<array{personId: string, role: string, orderNo: int}> $persons
     */
    public function __construct(
        public string $title,
        public string $description,
        public ?string $lyricsLink,
        public int $typeValue,
        public bool $isDisplay,
        public array $tags,
        public array $persons,
    ) {
    }
}
