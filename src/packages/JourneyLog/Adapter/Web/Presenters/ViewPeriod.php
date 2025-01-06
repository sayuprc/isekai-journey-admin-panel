<?php

declare(strict_types=1);

namespace JourneyLog\Adapter\Web\Presenters;

use DateTimeInterface;

class ViewPeriod
{
    public function __construct(private readonly DateTimeInterface $date)
    {
    }

    public function format(): string
    {
        return $this->date->format('Y-m-d');
    }
}
