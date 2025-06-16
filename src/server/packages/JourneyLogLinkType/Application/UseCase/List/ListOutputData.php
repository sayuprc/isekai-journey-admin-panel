<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\UseCase\List;

use JourneyLogLinkType\Domain\Models\JourneyLogLinkType;

class ListOutputData
{
    /**
     * @param JourneyLogLinkType[] $journeyLogLinkTypes
     */
    public function __construct(public readonly array $journeyLogLinkTypes)
    {
    }
}
