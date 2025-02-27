<?php

declare(strict_types=1);

namespace JourneyLogLinkType\UseCases\Create;

class CreateRequest
{
    public function __construct(
        public readonly string $journeyLogLinkTypeName,
        public readonly int $orderNo,
    ) {
    }
}
