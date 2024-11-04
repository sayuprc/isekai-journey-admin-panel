<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Port\UseCases\List;

use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;

class ListResponse
{
    /**
     * @param JourneyLogLinkType[] $journeyLogLinkTypes
     */
    public function __construct(public readonly array $journeyLogLinkTypes)
    {
    }
}
