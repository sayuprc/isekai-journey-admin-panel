<?php

declare(strict_types=1);

namespace JourneyLog\Application\UseCase\Delete;

readonly class DeleteInputData
{
    public function __construct(public string $journeyLogId)
    {
    }
}
