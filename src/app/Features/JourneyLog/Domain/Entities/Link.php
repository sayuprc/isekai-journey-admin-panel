<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Domain\Entities;

class Link
{
    public function __construct(
        public readonly LinkId $linkId,
        public readonly LinkName $linkName,
        public readonly Url $url,
        public readonly OrderNo $orderNo,
        public readonly LinkTypeId $linkTypeId,
    ) {
    }
}
