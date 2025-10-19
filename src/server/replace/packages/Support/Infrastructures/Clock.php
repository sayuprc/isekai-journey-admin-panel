<?php

declare(strict_types=1);

namespace Support\Infrastructures;

use Cake\Chronos\Chronos;
use DateTimeImmutable;
use Support\Contracts\ClockInterface;

readonly class Clock implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return Chronos::now();
    }
}
