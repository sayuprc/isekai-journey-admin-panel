<?php

declare(strict_types=1);

namespace Song\Domain\Models\Creators;

use Creator\Domain\Models\CreatorId;
use Support\Domain\ValueObjects\OrderNo;

readonly class Composer
{
    public function __construct(
        public CreatorId $creatorId,
        public OrderNo $orderNo,
    ) {
    }

    public static function reconstruct(string $creatorId, int $orderNo): self
    {
        return new self(CreatorId::reconstruct($creatorId), OrderNo::reconstruct($orderNo));
    }

    /**
     * @return array{creator_id: string, order_no: int}
     */
    public function toArray(): array
    {
        return [
            'creator_id' => $this->creatorId->value,
            'order_no' => $this->orderNo->value,
        ];
    }
}
