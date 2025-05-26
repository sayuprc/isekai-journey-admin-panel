<?php

declare(strict_types=1);

namespace JourneyLog\UseCases\Get;

class GetInputData
{
    public function __construct(public readonly string $journeyLogId)
    {
    }
}
