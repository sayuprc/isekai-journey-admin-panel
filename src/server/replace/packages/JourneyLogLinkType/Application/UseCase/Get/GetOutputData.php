<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\UseCase\Get;

use JourneyLogLinkType\Domain\Models\JourneyLogLinkType;

readonly class GetOutputData
{
    public function __construct(public JourneyLogLinkType $journeyLogLinkType)
    {
    }
}
