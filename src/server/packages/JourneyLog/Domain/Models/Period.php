<?php

declare(strict_types=1);

namespace JourneyLog\Domain\Models;

use Support\Domain\Exceptions\InvalidDomainException;

readonly class Period
{
    /**
     * @throws InvalidDomainException
     */
    public function __construct(
        public FromOn $fromOn,
        public ToOn $toOn,
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
