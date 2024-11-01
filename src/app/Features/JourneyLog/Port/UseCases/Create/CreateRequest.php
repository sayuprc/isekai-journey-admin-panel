<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Port\UseCases\Create;

class CreateRequest
{
    /**
     * @param array<array{link_name: string, url: string, order_no: int, link_type_id: string}> $links
     */
    public function __construct(
        public readonly string $story,
        public readonly string $fromOn,
        public readonly string $toOn,
        public readonly int $orderNo,
        public readonly array $links,
    ) {
    }
}
