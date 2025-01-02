<?php

declare(strict_types=1);

namespace JourneyLogLinkType\UseCases\Get;

class GetRequest
{
    public function __construct(public readonly string $journeyLogLinkTypeId)
    {
    }
}
