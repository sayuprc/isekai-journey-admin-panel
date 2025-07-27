<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\UseCase\List;

use JourneyLogLinkType\Domain\Models\JourneyLogLinkType;

readonly class ListOutputData
{
    /**
     * @param JourneyLogLinkType[] $journeyLogLinkTypes
     */
    public function __construct(public array $journeyLogLinkTypes)
    {
    }
}
