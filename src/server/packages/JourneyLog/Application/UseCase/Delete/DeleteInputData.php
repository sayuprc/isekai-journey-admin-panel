<?php

declare(strict_types=1);

namespace JourneyLog\Application\UseCase\Delete;

class DeleteInputData
{
    public function __construct(public readonly string $journeyLogId)
    {
    }
}
