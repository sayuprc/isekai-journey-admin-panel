<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Domain\Entities;

class LinkType
{
    public function __construct(
        public readonly LinkTypeId $linkTypeId,
        public readonly LinkTypeName $linkTypeName,
        public readonly OrderNo $orderNo,
    ) {
    }
}
