<?php

declare(strict_types=1);

namespace App\Shared\Domain;

use DateTimeInterface;

abstract class DateTimeValueObject
{
    public function __construct(public readonly DateTimeInterface $value)
    {
    }
}
