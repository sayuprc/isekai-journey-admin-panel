<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Create;

readonly class CreateInputData
{
    /**
     * @param list<array{creatorId: string}> $arrangers
     * @param list<array{creatorId: string}> $composers
     * @param list<array{creatorId: string}> $lyricists
     */
    public function __construct(
        public string $title,
        public string $description,
        public int $songTypeValue,
        public array $arrangers,
        public array $composers,
        public array $lyricists,
    ) {
    }
}
