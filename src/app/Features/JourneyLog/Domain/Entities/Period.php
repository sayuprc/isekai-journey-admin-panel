<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Domain\Entities;

use App\Features\JourneyLog\Domain\Exceptions\InvalidDomainException;
use DateTimeInterface;

class Period
{
    /**
     * @throws InvalidDomainException
     */
    public function __construct(
        public readonly DateTimeInterface $fromOn,
        public readonly DateTimeInterface $toOn
    ) {
        if ($this->toOn < $this->fromOn) {
            throw new InvalidDomainException('fromOn needs to be before toOn');
        }
    }

    public function isSingleDay(): bool
    {
        return $this->fromOn->format('Y-m-d') === $this->toOn->format('Y-m-d');
    }
}
