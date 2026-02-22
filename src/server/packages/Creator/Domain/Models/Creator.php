<?php

declare(strict_types=1);

namespace Creator\Domain\Models;

readonly class Creator
{
    public function __construct(
        public CreatorId $creatorId,
        public CreatorName $name,
    ) {
    }

    public static function reconstruct(string $creatorId, string $name): self
    {
        return new self(CreatorId::reconstruct($creatorId), CreatorName::reconstruct($name));
    }

    /**
     * @return array{creator_id: string, name: string}
     */
    public function toArray(): array
    {
        return [
            'creator_id' => $this->creatorId->value,
            'name' => $this->name->value,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->creatorId->equals($other->creatorId);
    }
}
