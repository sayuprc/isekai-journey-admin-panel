<?php

declare(strict_types=1);

namespace Support\Domain\ValueObjects\Boolean;

abstract readonly class BooleanValueObject
{
    public function __construct(public bool $value)
    {
    }
}
