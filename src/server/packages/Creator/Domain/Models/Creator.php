<?php

declare(strict_types=1);

namespace Creator\Domain\Models;

readonly class Creator
{
    public function __construct(
        public CreatorId $creatorId,
        public CreatorName $creatorName,
    ) {
    }

    public static function reconstruct(string $creatorId, string $creatorName): self
    {
        return new self(CreatorId::reconstruct($creatorId), CreatorName::reconstruct($creatorName));
    }

    /**
     * @return array{creator_id: string, creator_name: string}
     */
    public function toArray(): array
    {
        return [
            'creator_id' => $this->creatorId->value,
            'creator_name' => $this->creatorName->value,
        ];
    }
}
