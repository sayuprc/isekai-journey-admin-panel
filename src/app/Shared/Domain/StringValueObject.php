<?php

declare(strict_types=1);

namespace App\Shared\Domain;

class StringValueObject
{
    public function __construct(public readonly string $value)
    {
    }
}
