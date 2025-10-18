<?php

declare(strict_types=1);

namespace Support\Domain\ValueObjects\Numeric;

use Support\Domain\Exceptions\InvalidDomainException;

abstract readonly class PositiveIntegerValueObject extends IntegerValueObject
{
    /**
     * @param positive-int $value
     *
     * @throws InvalidDomainException
     */
    public function __construct(int $value)
    {
        // @phpstan-ignore smallerOrEqual.alwaysFalse
        if ($value <= 0) {
            throw new InvalidDomainException('Value must be a positive integer');
        }
        parent::__construct($value);
    }
}
