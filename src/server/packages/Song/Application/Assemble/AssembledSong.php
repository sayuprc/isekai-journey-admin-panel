<?php

declare(strict_types=1);

namespace Song\Application\Assemble;

readonly class AssembledSong
{
    /**
     * @param array<int, AssembledCreator> $lyricists
     * @param array<int, AssembledCreator> $composers
     * @param array<int, AssembledCreator> $arrangers
     */
    public function __construct(
        public string $songId,
        public string $title,
        public string $description,
        public string $typeName,
        public int $typeValue,
        public ?string $attributeName,
        public ?int $attributeValue,
        public int $orderNo,
        public bool $isDisplay,
        public array $lyricists,
        public array $composers,
        public array $arrangers,
    ) {
    }

    /**
     * @phpstan-assert-if-true !null $this->attributeName
     * @phpstan-assert-if-true !null $this->attributeValue
     */
    public function hasAttribute(): bool
    {
        return ! is_null($this->attributeName) && ! is_null($this->attributeValue);
    }
}
