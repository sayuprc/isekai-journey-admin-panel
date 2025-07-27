<?php

declare(strict_types=1);

namespace JourneyLog\Application\UseCase\Get;

use JourneyLog\Domain\Models\JourneyLog;

readonly class GetOutputData
{
    public function __construct(public JourneyLog $journeyLog)
    {
    }
}
