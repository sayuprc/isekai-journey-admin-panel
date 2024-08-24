<?php

declare(strict_types=1);

namespace App\Http\ViewModel;

class ViewDate
{
    public function __construct(
        private readonly int $year,
        private readonly int $month,
        private readonly int $day
    ) {
    }

    public function format(): string
    {
        return sprintf('%04s-%02s-%02s', $this->year, $this->month, $this->day);
    }

    public function equal(ViewDate $other): bool
    {
        return $this->year === $other->year
            && $this->month === $other->month
            && $this->day === $other->day;
    }
}
