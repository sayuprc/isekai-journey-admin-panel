<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Domain\Entities;

use DateTimeInterface;

class Period
{
    public function __construct(
        public readonly DateTimeInterface $fromOn,
        public readonly DateTimeInterface $toOn
    ) {
    }

    public function isSingleDay(): bool
    {
        return $this->fromOn->format('Y-m-d') === $this->toOn->format('Y-m-d');
    }
}
