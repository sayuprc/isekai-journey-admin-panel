<?php

declare(strict_types=1);

namespace App\Shared\Domain;

abstract class StringValueObject
{
    public function __construct(public readonly string $value)
    {
    }
}
