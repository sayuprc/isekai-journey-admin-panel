<?php

declare(strict_types=1);

namespace Performer\Domain\Models;

use Support\Domain\ValueObjects\OrderNo;

readonly class Performer
{
    public function __construct(
        public PerformerId $performerId,
        public PerformerName $name,
        public OrderNo $orderNo,
    ) {
    }

    public static function reconstruct(string $performerId, string $name, int $orderNo): self
    {
        return new self(
            PerformerId::reconstruct($performerId),
            PerformerName::reconstruct($name),
            OrderNo::reconstruct($orderNo),
        );
    }

    /**
     * @return array{performer_id: string, name: string, order_no: int}
     */
    public function toArray(): array
    {
        return [
            'performer_id' => $this->performerId->value,
            'name' => $this->name->value,
            'order_no' => $this->orderNo->value,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->performerId->equals($other->performerId);
    }
}
