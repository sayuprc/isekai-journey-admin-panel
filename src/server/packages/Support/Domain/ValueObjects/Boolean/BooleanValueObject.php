<?php

declare(strict_types=1);

namespace Support\Domain\ValueObjects\Boolean;

abstract class BooleanValueObject
{
    public function __construct(public readonly bool $value)
    {
    }
}
