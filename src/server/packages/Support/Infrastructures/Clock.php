<?php

declare(strict_types=1);

namespace Support\Infrastructures;

use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Support\Contracts\ClockInterface;

readonly class Clock implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new CarbonImmutable();
    }
}
