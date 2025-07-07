<?php

declare(strict_types=1);

namespace Support\Application;

use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Support\Contracts\ClockInterface;

class Clock implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new CarbonImmutable();
    }
}
