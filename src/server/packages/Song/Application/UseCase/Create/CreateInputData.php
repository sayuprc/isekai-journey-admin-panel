<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Create;

readonly class CreateInputData
{
    /**
     * @param list<array{creatorId: string}> $lyricists
     * @param list<array{creatorId: string}> $composers
     * @param list<array{creatorId: string}> $arrangers
     */
    public function __construct(
        public string $title,
        public string $description,
        public int $songTypeValue,
        public array $lyricists,
        public array $composers,
        public array $arrangers,
    ) {
    }
}
