<?php

declare(strict_types=1);

namespace JourneyLog\Application\UseCase\Get;

readonly class GetInputData
{
    public function __construct(public string $journeyLogId)
    {
    }
}
