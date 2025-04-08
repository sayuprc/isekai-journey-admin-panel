<?php

declare(strict_types=1);

namespace Support\Domain;

abstract class StringValueObject
{
    public function __construct(public readonly string $value)
    {
    }
}
