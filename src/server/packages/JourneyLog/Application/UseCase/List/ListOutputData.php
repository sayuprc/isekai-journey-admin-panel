<?php

declare(strict_types=1);

namespace JourneyLog\Application\UseCase\List;

use JourneyLog\Domain\Models\JourneyLog;

class ListOutputData
{
    /**
     * @param JourneyLog[] $journeyLogs
     */
    public function __construct(public readonly array $journeyLogs)
    {
    }
}
