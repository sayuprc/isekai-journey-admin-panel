<?php

declare(strict_types=1);

namespace JourneyLogLinkType\UseCases\Get;

use JourneyLogLinkType\Domain\Models\JourneyLogLinkType;

class GetResponse
{
    public function __construct(public readonly JourneyLogLinkType $journeyLogLinkType)
    {
    }
}
