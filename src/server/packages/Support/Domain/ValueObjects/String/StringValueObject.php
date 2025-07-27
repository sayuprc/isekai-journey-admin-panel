<?php

declare(strict_types=1);

namespace Support\Domain\ValueObjects\String;

abstract readonly class StringValueObject
{
    public function __construct(public string $value)
    {
    }
}
