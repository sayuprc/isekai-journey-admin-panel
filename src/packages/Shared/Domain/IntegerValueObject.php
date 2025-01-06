<?php

declare(strict_types=1);

namespace Shared\Domain;

abstract class IntegerValueObject
{
    public function __construct(public readonly int $value)
    {
    }
}
