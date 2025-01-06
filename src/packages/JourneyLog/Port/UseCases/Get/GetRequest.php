<?php

declare(strict_types=1);

namespace JourneyLog\Port\UseCases\Get;

class GetRequest
{
    public function __construct(public readonly string $journeyLogId)
    {
    }
}
