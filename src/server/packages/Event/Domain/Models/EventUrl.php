<?php

declare(strict_types=1);

namespace Event\Domain\Models;

use Support\Domain\ValueObjects\OrderNo;

readonly class EventUrl
{
    public function __construct(
        public EventUrlValue $url,
        public OrderNo $orderNo,
        public ?EventUrlLabel $label = null,
    ) {
    }

    public static function reconstruct(string $url, int $orderNo, ?string $label = null): self
    {
        return new self(
            new EventUrlValue($url),
            new OrderNo($orderNo),
            is_null($label) || $label === '' ? null : new EventUrlLabel($label),
        );
    }

    /**
     * @return array{url: string, order_no: int, label: ?string}
     */
    public function toArray(): array
    {
        return [
            'url' => $this->url->value,
            'order_no' => $this->orderNo->value,
            'label' => $this->label?->value,
        ];
    }
}
