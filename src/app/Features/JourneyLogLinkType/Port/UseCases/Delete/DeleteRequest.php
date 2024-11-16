<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Port\UseCases\Delete;

class DeleteRequest
{
    public function __construct(public readonly string $journeyLogLinkTypeId)
    {
    }
}
