<?php

declare(strict_types=1);

namespace JourneyLog\UseCases\List;

use JourneyLog\Domain\Models\JourneyLog;

class ListResponse
{
    /**
     * @param JourneyLog[] $journeyLogs
     */
    public function __construct(public readonly array $journeyLogs)
    {
    }
}
