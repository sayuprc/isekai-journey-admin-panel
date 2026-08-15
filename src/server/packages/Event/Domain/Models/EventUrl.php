<?php

declare(strict_types=1);

namespace Event\Domain\Models;

use Support\Domain\ValueObjects\OrderNo;

readonly class EventUrl
{
    public function __construct(
        public EventUrlValue $url,
        public OrderNo $orderNo,
    ) {
    }

    public static function reconstruct(string $url, int $orderNo): self
    {
        return new self(
            new EventUrlValue($url),
            new OrderNo($orderNo),
        );
    }

    /**
     * @return array{url: string, order_no: int}
     */
    public function toArray(): array
    {
        return [
            'url' => $this->url->value,
            'order_no' => $this->orderNo->value,
        ];
    }
}
