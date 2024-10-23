<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Domain\Entities;

class JourneyLogId
{
    public function __construct(public readonly string $value)
    {
    }
}
