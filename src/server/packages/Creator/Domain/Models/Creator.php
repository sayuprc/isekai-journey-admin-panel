<?php

declare(strict_types=1);

namespace Creator\Domain\Models;

use Support\Domain\ValueObjects\OrderNo;

readonly class Creator
{
    public function __construct(
        public CreatorId $creatorId,
        public CreatorName $name,
        public OrderNo $orderNo,
    ) {
    }

    public static function reconstruct(string $creatorId, string $name, int $orderNo): self
    {
        return new self(
            CreatorId::reconstruct($creatorId),
            CreatorName::reconstruct($name),
            OrderNo::reconstruct($orderNo),
        );
    }

    /**
     * @return array{creator_id: string, name: string, order_no: int}
     */
    public function toArray(): array
    {
        return [
            'creator_id' => $this->creatorId->value,
            'name' => $this->name->value,
            'order_no' => $this->orderNo->value,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->creatorId->equals($other->creatorId);
    }
}
