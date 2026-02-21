<?php

declare(strict_types=1);

namespace Performer\Domain\Models;

use Support\Domain\ValueObjects\OrderNo;

readonly class Performer
{
    public function __construct(
        public PerformerId $performerId,
        public PerformerName $performerName,
        public OrderNo $orderNo,
    ) {
    }

    public static function reconstruct(string $performerId, string $performerName, int $orderNo): self
    {
        return new self(
            PerformerId::reconstruct($performerId),
            PerformerName::reconstruct($performerName),
            OrderNo::reconstruct($orderNo),
        );
    }

    /**
     * @return array{performer_id: string, performer_name: string, order_no: int}
     */
    public function toArray(): array
    {
        return [
            'performer_id' => $this->performerId->value,
            'performer_name' => $this->performerName->value,
            'order_no' => $this->orderNo->value,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->performerId->equals($other->performerId);
    }
}
