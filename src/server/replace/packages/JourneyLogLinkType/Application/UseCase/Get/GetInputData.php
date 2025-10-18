<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\UseCase\Get;

readonly class GetInputData
{
    public function __construct(public string $journeyLogLinkTypeId)
    {
    }
}
