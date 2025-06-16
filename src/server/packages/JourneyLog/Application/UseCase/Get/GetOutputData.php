<?php

declare(strict_types=1);

namespace JourneyLog\Application\UseCase\Get;

use JourneyLog\Domain\Models\JourneyLog;

class GetOutputData
{
    public function __construct(public readonly JourneyLog $journeyLog)
    {
    }
}
