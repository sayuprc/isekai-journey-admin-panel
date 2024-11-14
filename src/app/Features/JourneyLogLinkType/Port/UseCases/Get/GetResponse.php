<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Port\UseCases\Get;

use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;

class GetResponse
{
    public function __construct(public readonly JourneyLogLinkType $journeyLogLinkType)
    {
    }
}
