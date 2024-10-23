<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Port\UseCases\List;

use App\Features\JourneyLog\Domain\Entities\JourneyLog;

class ListResponse
{
    /**
     * @param array<JourneyLog> $journeyLogs
     */
    public function __construct(public readonly array $journeyLogs)
    {
    }
}
