<?php

declare(strict_types=1);

namespace JourneyLogLinkType\UseCases\Delete;

class DeleteRequest
{
    public function __construct(public readonly string $journeyLogLinkTypeId)
    {
    }
}
