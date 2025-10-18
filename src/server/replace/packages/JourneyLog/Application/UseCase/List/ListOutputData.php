<?php

declare(strict_types=1);

namespace JourneyLog\Application\UseCase\List;

use JourneyLog\Domain\Models\JourneyLog;

readonly class ListOutputData
{
    /**
     * @param JourneyLog[] $journeyLogs
     */
    public function __construct(public array $journeyLogs)
    {
    }
}
