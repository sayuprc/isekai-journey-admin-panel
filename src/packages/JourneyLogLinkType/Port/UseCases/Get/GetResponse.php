<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Port\UseCases\Get;

use JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;

class GetResponse
{
    public function __construct(public readonly JourneyLogLinkType $journeyLogLinkType)
    {
    }
}
