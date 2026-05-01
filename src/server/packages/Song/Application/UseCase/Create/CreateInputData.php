<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Create;

readonly class CreateInputData
{
    /**
     * @param list<array{creatorId: string}> $lyricists
     * @param list<array{creatorId: string}> $composers
     * @param list<array{creatorId: string}> $arrangers
     * @param list<array{songTagId: string}> $tags
     */
    public function __construct(
        public string $title,
        public string $description,
        public int $typeValue,
        public bool $isDisplay,
        public array $lyricists,
        public array $composers,
        public array $arrangers,
        // TODO デフォルト null をどうにかする
        public ?int $attributeValue = null,
        public array $tags = [],
    ) {
    }
}
