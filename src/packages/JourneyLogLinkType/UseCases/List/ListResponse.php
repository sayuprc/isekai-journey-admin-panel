<?php

declare(strict_types=1);

namespace JourneyLogLinkType\UseCases\List;

use JourneyLogLinkType\Domain\Models\JourneyLogLinkType;

class ListResponse
{
    /**
     * @param JourneyLogLinkType[] $journeyLogLinkTypes
     */
    public function __construct(public readonly array $journeyLogLinkTypes)
    {
    }
}
