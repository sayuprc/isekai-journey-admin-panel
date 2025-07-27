<?php

declare(strict_types=1);

namespace Support\Domain\ValueObjects\Numeric;

abstract readonly class IntegerValueObject
{
    public function __construct(public int $value)
    {
    }
}
