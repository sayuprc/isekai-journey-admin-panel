<?php

declare(strict_types=1);

namespace JourneyLog\Port\UseCases\List;

use JourneyLog\Domain\Entities\JourneyLog;

class ListResponse
{
    /**
     * @param JourneyLog[] $journeyLogs
     */
    public function __construct(public readonly array $journeyLogs)
    {
    }
}
