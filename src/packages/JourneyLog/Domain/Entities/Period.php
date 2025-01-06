<?php

declare(strict_types=1);

namespace JourneyLog\Domain\Entities;

use App\Shared\Domain\Exceptions\InvalidDomainException;

class Period
{
    /**
     * @throws InvalidDomainException
     */
    public function __construct(
        public readonly FromOn $fromOn,
        public readonly ToOn $toOn
    ) {
        if ($this->toOn->value < $this->fromOn->value) {
            throw new InvalidDomainException('fromOn needs to be before toOn');
        }
    }

    public function isSingleDay(): bool
    {
        return $this->fromOn->value->format('Y-m-d') === $this->toOn->value->format('Y-m-d');
    }
}
