<?php

declare(strict_types=1);

namespace JourneyLog\UseCases\Delete;

class DeleteRequest
{
    public function __construct(public readonly string $journeyLogId)
    {
    }
}
