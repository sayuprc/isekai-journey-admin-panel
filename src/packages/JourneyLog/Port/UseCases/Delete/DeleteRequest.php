<?php

declare(strict_types=1);

namespace JourneyLog\Port\UseCases\Delete;

class DeleteRequest
{
    public function __construct(public readonly string $journeyLogId)
    {
    }
}
