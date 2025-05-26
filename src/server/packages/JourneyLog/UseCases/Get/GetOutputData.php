<?php

declare(strict_types=1);

namespace JourneyLog\UseCases\Get;

use JourneyLog\Domain\Models\JourneyLog;

class GetOutputData
{
    public function __construct(public readonly JourneyLog $journeyLog)
    {
    }
}
