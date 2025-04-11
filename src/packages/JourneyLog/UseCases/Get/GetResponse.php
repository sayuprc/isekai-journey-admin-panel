<?php

declare(strict_types=1);

namespace JourneyLog\UseCases\Get;

use JourneyLog\Domain\Models\JourneyLog;

class GetResponse
{
    public function __construct(public readonly JourneyLog $journeyLog)
    {
    }
}
