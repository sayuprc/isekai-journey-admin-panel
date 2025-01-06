<?php

declare(strict_types=1);

namespace JourneyLogLinkType\UseCases\List;

use JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;

class ListResponse
{
    /**
     * @param JourneyLogLinkType[] $journeyLogLinkTypes
     */
    public function __construct(public readonly array $journeyLogLinkTypes)
    {
    }
}
