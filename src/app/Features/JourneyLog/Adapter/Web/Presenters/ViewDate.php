<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Adapter\Web\Presenters;

use DateTimeInterface;

class ViewDate
{
    public function __construct(private readonly DateTimeInterface $date)
    {
    }

    public function format(): string
    {
        return $this->date->format('Y-m-d');
    }

    public function equal(ViewDate $other): bool
    {
        return $this->format() === $other->format();
    }
}
