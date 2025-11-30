<?php

declare(strict_types=1);

namespace Performer\Application\UseCase\Get;

readonly class GetInputData
{
    public function __construct(public string $performerId)
    {
    }
}
