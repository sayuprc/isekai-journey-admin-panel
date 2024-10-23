<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Port\UseCases\Get;

use App\Features\JourneyLog\Domain\Entities\JourneyLog;

class GetResponse
{
    public function __construct(public readonly JourneyLog $journeyLog)
    {
    }
}
