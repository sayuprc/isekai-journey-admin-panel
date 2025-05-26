<?php

declare(strict_types=1);

namespace Support\Domain\ValueObjects\Numeric;

abstract class IntegerValueObject
{
    public function __construct(public readonly int $value)
    {
    }
}
